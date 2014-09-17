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

        $fragment = $config->getFragments($providerName);
        if ($fragment) {
            $modules = $fragment['modules'];
        }
        else {
            $module = $config->getModules($providerName);
            if ($module !== null) {
                $modules = [ $module ];
            }
            else {
                throw new Exception("Provider \"{$providerName}\" did not match any module at {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
            }
        }

        if (isset($modules)) {
            $output = '';
            $maxAge = 0;
            if (isset($fragment['template'])) {
                $tpl = $this->sl->getTemplate();
            }
            foreach ($modules as $module) {
                if (!isset($module['options']))
                    $module['options'] = array();
                
                // Get module object
                $object = AetherModuleFactory::create($module['name'], 
                        $this->sl, $module['options'] + $options);

                if ($object->getCacheTime() !== null)
                    $maxAge = min($object->getCacheTime(), $maxAge);

                if (isset($module['cache']))
                    $maxAge = min($module['cache'], $maxAge);

                if (isset($fragment['template'])) {
                    $this->provide($module['provides'], $object->run());
                }
                else {
                    $output .= $object->run();
                }
            }
            if (isset($fragment['template'])) {
                $output = $tpl->fetch($fragment['template']);
            }
            
            if ($maxAge > 0) {
                header("Cache-Control: s-maxage={$maxAge}");
            } 
            print $output;
        }

    }
    
    private function preloadModules($modules, $options) {
        // Preload modules, set cachetime and find minimum page cache time
        foreach ($modules as &$module) {
            if (!isset($module['options']))
                $module['options'] = array();
            $object = "";
            // Get module object
            try {
                $object = AetherModuleFactory::create($module['name'], 
                        $this->sl, $module['options'] + $options);
                
                // If the module, in this setting, blocks caching, accept
                if ($this->cache && ($cachetime = $object->getCacheTime()) !== null) {
                    $module['cache'] = $cachetime;

                    // Reset page cache time to module since we ask for stuff
                    // to be updated at an earlier interval
                    $this->pageCacheTime = min($this->pageCacheTime, $module['cache']);
                }

                $module['obj'] = $object;
            }
            catch (Exception $e) {
                $this->logerror($e);
            }
        }

        return $modules;
    }

    private function loadModule($module) {
        if ($this->cache && array_key_exists('cache', $module) && $module['cache'] > 0) {
            $mCacheName = $this->cacheName . $module['name'] ;

            if (isset($module['provides']))
                $mCacheName .= $module['provides'];

            if (array_key_exists('cacheas', $module)) {
                $mCacheName = $url->get('host') . $module['cacheas'];
            }

            // Try to read from cache, else generate and cache
            if (($mOut = $this->cache->get($mCacheName)) == false) {
                if (isset($module['obj'])) {
                    $mod = $module['obj'];
                    $mCacheTime = $module['cache'];

                    try {
                        $module['output'] = $mod->run();
                        if (is_numeric($mCacheTime) && $mCacheTime > 0) {
                            $this->cache->set($mCacheName, $mOut, $mCacheTime);
                        }
                        else {
                            $this->pageCacheTime = 0;
                        }
                    }
                    catch (Exception $e) {
                        $this->logerror($e);
                    }
                }
            }
        }
        else {
            // Module shouldn't be cached, just render it without
            // saving to cache
            if (isset($module['obj'])) {
                $mod = $module['obj'];

                try {
                    $module['output'] = $mod->run();
                }
                catch (Exception $e) {
                    $this->logerror($e);
                    return false;
                }
            }
        }

        return $module;
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
        $this->cache = $this->sl->has("cache") ? $this->sl->get("cache") : false;
        $cacheable = true;
        /** 
         * Decide cache name for rule based cache
         * If the option cacheas is set, we will use the cache name
         * $domainname_$cacheas
         */
        $url = $this->sl->get('parsedUrl');
        if ($this->cache) {
            $cacheas = $config->getCacheName();
            if ($cacheas != false)
                $this->cacheName = $url->get('host') . '_' . $cacheas;
            else
                $this->cacheName = $url->cacheName();

            $this->pageCacheTime = $config->getCacheTime();
            if ($this->pageCacheTime === false) 
                $this->pageCacheTime = 0;

            if ($url->get('query') != "")
                $cacheable = false;
        }
        else {
            $this->pageCacheTime = 0;
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

        // Cache complete pages in Aether. Does not affect module cache
        if (isset($options['cachePages']) && $options['cachePages'] == 'false') {
            $cachePages = false;
        }
        else {
            $cachePages = true;
        }
        
        $lc_numeric = (isset($options['lc_numeric'])) ? $options['lc_numeric'] : 'C';
        setlocale(LC_NUMERIC, $lc_numeric);

        // Support custom searchpaths
        $searchPath = (isset($options['searchpath'])) 
            ? $options['searchpath'] : $this->sl->get("aetherPath");
        AetherModuleFactory::$path = $searchPath;

        $modules = $this->preloadModules($config->getModules(), $options);

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
        $cacheable = ($cacheable && is_object($this->cache));
        if (!$cachePages || !$cacheable || $this->pageCacheTime === 0 || ($this->cache->get($this->cacheName) == false)) {
            /* Load controller template
             * This template knows where all modules should be placed
             * and have internal wrapping html for this section
             */
            $tplInfo = $config->getTemplate();
            $tpl = $this->sl->getTemplate();
            if (is_array($modules)) {
                $tpl->set("extras", $tplVars);

                foreach ($modules as &$module) {
                    // If module should be cached, handle it
                    $module = $this->loadModule($module);
                    if (!$module)
                        continue;

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
                    if (isset($timer) AND is_object($timer)) {

                        $timer->tick('module_run', $modId);
                    }
                }
            }

            foreach ($config->getFragments() as $frag) {
                foreach (array_keys($frag['modules']) as $mod) 
                    $tpl->set($modules[$mod]['provides'], $modules[$mod]['output']);
                $this->provide($frag['provides'], $tpl->fetch($frag['template']));
            }
            if (!isset($tplInfo['name']) || strlen($tplInfo['name']) === 0) {
                throw new AetherConfigErrorException("Template not specified for url: " . (string)$url);
            }
            else {
                $output = $tpl->fetch($tplInfo['name']);
            }

            if ($cachePages && $cacheable && $this->pageCacheTime > 0) {
                $this->cache->set($this->cacheName, $output, $this->pageCacheTime);
            }
        }
        else {
            $output = $this->cache->get($this->cacheName);
        }

        // Page is cacheable even with cachePages off since we want headers for 
        // browser and ex. varnish etc.
        if (is_numeric($this->pageCacheTime)) {
            header("Cache-Control: s-maxage={$this->pageCacheTime}");
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
    public function service($name, $serviceName, $type = 'module') {
        // Locate module containing service
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();
        // Support custom searchpaths
        $searchPath = (isset($options['searchpath'])) 
            ? $options['searchpath'] : $this->sl->get("aetherPath");
        AetherModuleFactory::$path = $searchPath;

        $locale = (isset($options['locale'])) ? $options['locale'] : "nb_NO.UTF-8";
        setlocale(LC_ALL, $locale);

        $lc_numeric = (isset($options['lc_numeric'])) ? $options['lc_numeric'] : 'C';
        setlocale(LC_NUMERIC, $lc_numeric);

        if ($type == 'fragment') {
            $fragment = $config->getFragments($name);
            $moduleNames = isset($fragment['modules']) ? array_keys($fragment['modules']) : [];
        }
        else {
            $moduleNames = [ $name ];
        }

        // Create module
        $mod = null;
        $modules = [];
        $configModules = $config->getModules();
        $configModuleNames = array_map(function ($mod) { return $mod['name']; }, $configModules);

        foreach ($moduleNames as $moduleName) {
            if (isset($configModules[$moduleName])) {
                $module = $configModules[$moduleName];
            }
            elseif (in_array($moduleName, $configModuleNames)) {
                foreach ($configModules as $m) {
                    if ($m['name'] == $moduleName) {
                        $module = $m;
                        break;
                    }
                }
            }

            if (!isset($module['options']))
                $module['options'] = array();
            $opts = $module['options'] + $options;
            if (array_key_exists('session', $opts) 
                        AND $opts['session'] == 'on') {
                session_start();
            }
            // Get module object
            $mod = AetherModuleFactory::create($moduleName, $this->sl, $opts);
            if ($type == 'module') {
                $modules = [ $mod ];
                break;
            }
            else {
                $modules[$moduleName] = $mod;
            }
        }
        // Run service
        $moduleResponses = [];
        foreach ($modules as $id => $mod) {
            if ($mod instanceof AetherModule) {
                // Run service
                if ($type == 'module') {
                    return $mod->service($serviceName);
                }
                else {
                    $moduleResponses[$id] = $mod->service($serviceName);
                }
            }
            else {
                throw new Exception("Service run error: Failed to locate {$type} [$name], check if it is loaded in config for this url: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . (isset($_SERVER['HTTP_REFERER']) ? ", called from URI: " . $_SERVER['HTTP_REFERER'] : ""));
            }
        }
        return new AetherFragmentResponse($moduleResponses);
    }
    
    /**
     * Provide the output of a module
     *
     * @return void
     * @param string $name
     * @param string $content
     */
    private function provide($name, $content) {
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
        trigger_error("Caught exception at " . $e->getFile() . ":" . $e->getLine() . ": " . $e->getMessage() . ", trace: " . str_replace("\n", ", ", $e->getTraceAsString()));
    }
}
