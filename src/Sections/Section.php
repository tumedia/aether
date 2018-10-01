<?php

namespace Aether\Sections;

use Error;
use Throwable;
use Aether\Aether;
use Aether\Response\Text;
use Aether\Modules\Module;
use Aether\Modules\ModuleFactory;
use Aether\Exceptions\ConfigError;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Aether\Exceptions\ServiceNotFound;
use InvalidArgumentException;

/**
 * Base class definition of aether sections
 *
 * Created: 2007-02-05
 * @author Raymond Julin
 * @package aether.lib
 */
abstract class Section
{
    /**
     * The Aether instance.
     *
     * @var \Aether\Aether
     */
    protected $aether;

    /**
     * Legacy alias for the $aether property.
     *
     * @var \Aether\Aether
     */
    protected $sl;

    /**
     * The Module Factory instance.
     *
     * @var \Aether\Modules\ModuleFactory
     */
    protected $moduleFactory;

    /**
     * Cache time for varnish
     *
     * @var integerino
     */
    protected $pageCacheTime;

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
        $this->sl = $this->aether;
        $this->moduleFactory = $aether->make(ModuleFactory::class);
    }

    private function preloadModules($modules, $options)
    {
        // Preload modules, set cachetime and find minimum page cache time
        foreach ($modules as &$module) {
            if (!isset($module['options'])) {
                $module['options'] = array();
            }
            $object = "";
            // Get module object
            try {
                $object = $this->moduleFactory->create(
                    $module['name'],
                    $module['options'] + $options
                );

                // If the module, in this setting, blocks caching, accept
                if ($this->cache && ($cachetime = $object->getCacheTime()) !== null) {
                    $module['cache'] = $cachetime;

                    // Reset page cache time to module since we ask for stuff
                    // to be updated at an earlier interval
                    $this->pageCacheTime = min($this->pageCacheTime, $module['cache']);
                }

                $module['obj'] = $object;
            } catch (Throwable $e) {
                $this->handleModuleError($e);
            }
        }

        return $modules;
    }

    private function loadModule($module)
    {
        if ($this->cache && array_key_exists('cache', $module) && $module['cache'] > 0) {
            $mCacheName = $this->cacheName . $module['name'] ;

            if (isset($module['provides'])) {
                $mCacheName .= $module['provides'];
            }

            if (isset($module['obj'])) {
                $mCacheName = $module['obj']->getCacheKey($mCacheName);
            }

            // Try to read from cache, else generate and cache
            if (($mOut = $this->cache->get($mCacheName)) == false) {
                if (isset($module['obj'])) {
                    $mod = $module['obj'];
                    $mCacheTime = $module['cache'];

                    try {
                        $mOut = $this->moduleFactory->run($mod);
                        if (is_numeric($mCacheTime) && $mCacheTime > 0) {
                            $this->cache->set($mCacheName, $mOut, $mCacheTime);
                        } else {
                            // uncacbleable page if at least one module is uncacheable
                            $this->pageCacheTime = 0;
                        }
                    } catch (Throwable $e) {
                        $this->handleModuleError($e);
                    }
                }
            }
        } else {
            // Module shouldn't be cached, just render it without
            // saving to cache
            if (isset($module['obj'])) {
                $mod = $module['obj'];

                try {
                    $mOut = $this->moduleFactory->run($mod);
                } catch (Throwable $e) {
                    $this->handleModuleError($e);
                    return false;
                }
            }
        }

        $module['output'] = $mOut ?? '';

        return $module;
    }

    /**
     * Render content from modules
     * this is where caching is implemented
     * TODO Possible refactoring, many leves of nesting
     *
     * @return string
     */
    protected function renderModules()
    {
        if ($this->aether->bound('timer')) {
            $timer = $this->aether['timer'];

            $timer->start('module_run');
        }

        $config = $this->aether['aetherConfig'];
        $this->cache = $this->aether->bound('cache') ? $this->aether['cache'] : false;
        $this->pageCacheTime = $config->getCacheTime();

        /**
         * Decide cache name for rule based cache
         * If the option cacheas is set, we will use the cache name
         * $domainname_$cacheas
         */
        $url = $this->aether['parsedUrl'];
        if ($this->cache) {
            $cacheas = $config->getCacheName();
            if ($cacheas != false) {
                $this->cacheName = $url->get('host') . '_' . $cacheas;
            } else {
                $this->cacheName = $url->cacheName();
            }
        }

        /**
         * If one object requests no cache of this request
         * then we need to take that into consideration.
         * If the application frontend and adminpanel lives
         * at the same URL, its crucial that the admin part is
         * not cached and later on displayed to an end user
         */
        $options = $config->getOptions();

        $modules = $this->preloadModules($config->getModules(), $options);

        /**
         * If we have a timer, end this timing
         * we're in test mode and thus showing timing
         * information
         */
        if (isset($timer) and is_object($timer)) {
            $timer->tick('module_run', 'read_config');
        }

        /**
         * Render page
         */

        /* Load controller template
         * This template knows where all modules should be placed
         * and have internal wrapping html for this section
         */
        $tplInfo = $config->getTemplate();
        $tpl = $this->aether->getTemplate();
        if (is_array($modules)) {
            foreach ($modules as &$module) {
                // If module should be cached, handle it
                $module = $this->loadModule($module);
                if (!$module) {
                    continue;
                }

                /**
                 * Support multiple modules of same type by
                 * specificaly naming them with a surname when
                 * duplicates are encountered
                 */
                $modId = isset($module['provides']) ? $module['provides'] : $module['name'];

                $this->provide($modId, $module['output']);
                // DEPRECATED: direct access to $ModuleName in template
                $tpl->set($module['name'], $module['output']);

                /**
                 * If we have a timer, end this timing
                 * we're in test mode and thus showing timing
                 * information
                 */
                if (isset($timer) and is_object($timer)) {
                    $timer->tick('module_run', $modId);
                }
            }
        }

        if (empty($tplInfo['name'])) {
            throw new ConfigError("Template not specified for url: " . (string)$url);
        }

        $output = $tpl->fetch($tplInfo['name']);

        // Varnish cache header
        if (is_numeric($this->pageCacheTime) && !headers_sent()) {
            header("Cache-Control: s-maxage={$this->pageCacheTime}");
        }

        /**
         * If we have a timer, end this timing
         * we're in test mode and thus showing timing
         * information
         */
        if (isset($timer) and is_object($timer)) {
            $timer->end('module_run');
        }
        // Return output
        return $output;
    }

    /**
     * Render this section
     * Returns a Response object which can contain a text response or
     * a header redirect response
     * The advantages to using response objects is to more cleanly
     * supporting header() redirects. In other words; more response
     * types
     *
     * @access public
     * @return AetherResponse
     */
    abstract public function response();

    /**
     * Render service
     *
     * @access public
     * @return AetherResponse
     * @param string $moduleName
     * @param string $serviceName Name of service
     */
    public function service($name, $serviceName)
    {
        // Locate module containing service
        $config = $this->aether['aetherConfig'];
        $options = $config->getOptions();

        // Create module
        $mod = null;
        $configModules = $config->getModules();
        $configModuleNames = array_map(function ($mod) {
            return $mod['name'];
        }, $configModules);

        if (isset($configModules[$name])) {
            $module = $configModules[$name];
        } elseif (in_array($name, $configModuleNames)) {
            foreach ($configModules as $m) {
                if ($m['name'] == $name) {
                    $module = $m;
                    break;
                }
            }
        } else {
            $module = array('name' => $name);
        }

        if (!isset($module['options'])) {
            $module['options'] = array();
        }
        $opts = $module['options'] + $options;
        if (isset($opts['session']) && $opts['session'] == 'on') {
            session_start();
        }

        // Get module object
        try {
            $mod = $this->moduleFactory->create($module['name'], $opts);
        }
        catch (InvalidArgumentException $e) {
            throw new ServiceNotFound($e->getMessage(), 0, $e);
        }

        return $mod->service($serviceName);
    }

    /**
     * Provide the output of a module
     *
     * @return void
     * @param string $name
     * @param string $content
     */
    private function provide($name, $content)
    {
        $vector = $this->aether->getVector('aetherProviders');
        $vector[$name] = $content;
    }

    /**
     * Handle exceptions thrown by modules. In production, the exception is
     * simply reported to the exception handler without being rendered.
     *
     * @param  \Throwable  $e
     * @return void
     * @throws \Exception
     */
    private function handleModuleError(Throwable $e)
    {
        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }

        if (! $this->aether->isProduction()) {
            throw $e;
        }

        resolve(ExceptionHandler::class)->report($e);
    }

    public function triggerDefaultRule()
    {
        $config = $this->aether['aetherConfig'];
        $config->reloadConfigFromDefaultRule();
        $section = SectionFactory::create(
            $config->getSection(),
            $this->aether
        );
        return $section->response();
    }
}
