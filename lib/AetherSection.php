<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * Base class definition of aether sections
 * 
 * Created: 2007-02-05
 * @author Raymond Julin
 * @package aether.lib
 */

abstract class AetherSection {
    
    /**
     * Hold service locator
     * @var AetherServiceLocator
     */
    protected $sl = null;
    
    /**
     * COnstructor. Accept subsection
     *
     * @access public
     * @return AetherSection
     * @param AetherServiceLocator $sl
     */
    public function __construct(AetherServiceLocator $sl) {
        $this->sl = $sl;
    }

    /**
     * Render one module based on its provider name.
     * Adds headers for cache time if cache attribute is specified.
     *
     * @access public
     * @param string $providerName
     */
    public function renderProviderWithCacheHeaders($providerName) {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        // Support custom searchpaths
        $searchPath = (isset($options['searchpath'])) 
            ? $options['searchpath'] : $this->sl->get("aetherPath");
        AetherModuleFactory::$path = $searchPath;
        $mods = $config->getModules();

        foreach ($mods as $m) {
            if ($m['provides'] === $providerName) {
                $module = $m;
                break;
            }
        }

        if (isset($module)) {
            if (!isset($module['options']))
                $module['options'] = array();
            // Get module object
            $object = AetherModuleFactory::create($module['name'], 
                    $this->sl, $module['options']);

            // Check if module overrides cache time
            if (($cachetime = $object->getCacheTime()) !== null) {
                header("Cache-Control: max-age={$cacheTime}");
            } 
            else if (isset($module['cache'])) {
                header("Cache-Control: max-age={$module['cache']}");
            }

            print $object->run();
        }
    }
    
    /**
     * Render content from modules
     * this is where caching is implemented
     * TODO Possible refactoring, many leves of nesting
     * TODO Reconsider the solution with passing in extra tpl data
     * to renderModules() as an argument. Smells bad
     *
     * @access protected
     * @return string
     * @param array $tplVars
     */
    protected function renderModules($tplVars = array()) {
        try {
            // Timer
            $timer = $this->sl->get('timer');
            $timer->start('module_run');
        }
        catch (Exception $e) {
            // No timing, we're in prod
        }
        $config = $this->sl->get('aetherConfig');
        if ($this->sl->has("cache"))
            $cache = $this->sl->get("cache");
        else
            $cache = false;
        $cacheable = true;
        /** 
         * Decide cache name for rule based cache
         * If the option cacheas is set, we will use the cache name
         * $domainname_$cacheas
         */
        $url = $this->sl->get('parsedUrl');
        if ($cache) {
            $cacheas = $config->getCacheName();
            if ($cacheas != false)
                $cacheName = $url->get('host') . '_' . $cacheas;
            else
                $cacheName = $url->cacheName();

            $pageCacheTime = $config->getCacheTime();
            if ($pageCacheTime === false) 
                $pageCacheTime = 0;

            if ($url->get('query') != "")
                $cacheable = false;
        }
        else {
            $pageCacheTime = 0;
        }

        /**
         * If one object requests no cache of this request
         * then we need to take that into consideration.
         * If the application frontend and adminpanel lives
         * at the same URL, its crucial that the admin part is
         * not cached and later on displayed to an end user
         */
        $options = $config->getOptions();
        // Support i18n
        $locale = (isset($options['locale'])) ? $options['locale'] : "nb_NO.UTF-8";
        setlocale(LC_ALL, $locale);
        
        $lc_numeric = (isset($options['lc_numeric'])) ? $options['lc_numeric'] : 'C';
        setlocale(LC_NUMERIC, $lc_numeric);

        // Support custom searchpaths
        $searchPath = (isset($options['searchpath'])) 
            ? $options['searchpath'] : $this->sl->get("aetherPath");
        AetherModuleFactory::$path = $searchPath;

        $modules = $config->getModules();
        foreach ($modules as &$module) {
            if (!isset($module['options']))
                $module['options'] = array();
            // Get module object
            $object = AetherModuleFactory::create($module['name'], 
                    $this->sl, $module['options']);
            // If the module, in this setting, blocks caching, accept
            if ($cache && ($cachetime = $object->getCacheTime()) !== null) {
                $module['cache'] = $cachetime;

                // Reset page cache time to module since we ask for stuff
                // to be updated at an earlier interval
                $pageCacheTime = min($pageCacheTime, $module['cache']);
            }

            $module['obj'] = $object;
        }
        /**
         * If we have a timer, end this timing
         * we're in test mode and thus showing timing
         * information
         */
        if (isset($timer) AND is_object($timer))
            $timer->tick('module_run', 'read_config');


        /**
         * Render page
         */
        $cacheable = ($cacheable && is_object($cache));
        if (!$cacheable || $pageCacheTime === 0 || ($cache->get($cacheName) == false)) {
            /* Load controller template
             * This template knows where all modules should be placed
             * and have internal wrapping html for this section
             */
            $tplInfo = $config->getTemplate();
            $tpl = $this->sl->getTemplate();
            if (is_array($modules)) {
                $tpl->set("extras", $tplVars);
                $modulesOut = array();
                foreach ($modules as &$module) {
                    // If module should be cached, handle it
                    if ($cache && array_key_exists('cache', $module) && $module['cache'] > 0) {
                        $mCacheName = $cacheName . $module['name'] ;

                        if ($module['provides'])
                            $mCacheName .= $module['provides'];

                        if (array_key_exists('cacheas', $module)) {
                            $mCacheName = $url->get('host') . $module['cacheas'];
                        }

                        // Try to read from cache, else generate and cache
                        if (($mOut = $cache->get($mCacheName)) == false) {
                            $mCacheTime = $module['cache'];
                            $mod = $module['obj'];

                            try {
                                $mOut = $mod->run();
                                if (is_numeric($mCacheTime) && $mCacheTime > 0) {
                                    $cache->set($mCacheName, $mOut, $mCacheTime);
                                }
                                else {
                                    $pageCacheTime = 0;
                                }
                            }
                            catch (Exception $e) {
                                $pageCacheTime = 0;
                                $this->logerror($e);
                            }
                        }
                    }
                    else {
                        // Module shouldn't be cached, just render it without
                        // saving to cache
                        $mod = $module['obj'];

                        try {
                            $mOut = $mod->run();
                        }
                        catch (Exception $e) {
                            $pageCacheTime = 0;
                            $this->logerror($e);
                            continue;
                        }
                    }
                    /**
                     * If this module provides some service
                     * make sure we actually push it
                     */
                    if (array_key_exists('provides', $module)) {
                        $this->provide($module['provides'], $mOut);
                    }

                    /**
                     * Support multiple modules of same type by 
                     * specificaly naming them with a surname when
                     * duplicates are encountered
                     */
                    $modName = $module['name'];

                    if (!isset($modulesOut[$modName])) {
                        $modulesOut[$modName] = array();
                    }

                    if (array_key_exists('provides', $module)) {
                        $modulesOut[$modName][$module['provides']] = $mOut;
                    }
                    else {
                        $modulesOut[$modName][] = $mOut;
                    }

                    /**
                     * If we have a timer, end this timing
                     * we're in test mode and thus showing timing
                     * information
                     */
                    if (isset($timer) AND is_object($timer)) {
                        if (array_key_exists('provides', $module))
                            $timerMsg = $module['provides'];
                        else
                            $timerMsg = $modName;

                        $timer->tick('module_run', $timerMsg);
                    }
                }
                // Export rendered modules to template
                foreach ($modulesOut as $name => $mod) {
                    $name = str_replace('/', '_', $name);

                    if (count($mod) > 1) {
                        $tpl->set($name, $mod);
                    }
                    else {
                        $tpl->set($name, current($mod));
                    }
                }
            }
            if (!isset($tplInfo['name']) || strlen($tplInfo['name']) === 0) {
                throw new AetherConfigErrorException("Template not specified for url: " . (string)$url);
            }
            else {
                $output = $tpl->fetch($tplInfo['name']);
            }

            if ($cacheable && $pageCacheTime > 0) {
                $cache->set($cacheName, $output, $pageCacheTime);
            }
        }
        else {
            $output = $cache->get($cacheName);
        }

        if ($cacheable) {
            header("Cache-Control: max-age={$pageCacheTime}");
        }

        /**
         * If we have a timer, end this timing
         * we're in test mode and thus showing timing
         * information
         */
        if (isset($timer) AND is_object($timer))
            $timer->end('module_run');
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
    public function service($moduleName, $serviceName) {
        // Locate module containing service
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();
        // Support custom searchpaths
        $searchPath = (isset($options['searchpath'])) 
            ? $options['searchpath'] : $this->sl->get("aetherPath");
        AetherModuleFactory::$path = $searchPath;

        // Create module
        $mod = null;
        foreach ($config->getModules() as $module) {
            if ($module['name'] != $moduleName)
                continue;
            if (!isset($module['options']))
                $module['options'] = array();
            $opts = $module['options'] + $options;
            if (array_key_exists('session', $opts) 
                        AND $opts['session'] == 'on') {
                session_start();
            }
            // Get module object
            $mod = AetherModuleFactory::create($module['name'], 
                    $this->sl, $module['options']);
            break;
        }
        // Run service
        if ($mod instanceof AetherModule) {
            // Run service
            return $mod->service($serviceName);
        }
        throw new Exception("Service run error: Failed to locate module [$moduleName], check if it is loaded in config for this url: " . $_SERVER['SCRIPT_URI']);
    }
    
    /**
     * Provide the output of a module
     *
     * @access public
     * @return void
     * @param string $name
     * @param string $content
     */
    public function provide($name, $content) {
        $vector = $this->sl->getVector('aetherProviders');
        $vector[$name] = $content;
    }
    
    /**
     * Log an error message from an exception to error log
     *
     * @access private
     * @return void
     * @param Exception $e
     */
    private function logerror($e) {
        trigger_error("Caught exception at " . $e->getFile() . ":" . $e->getLine() . ": " . $e->getMessage() . ", trace: " . $e->getTraceAsString());
    }
}
