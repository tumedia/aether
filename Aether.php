<?php // vim:set ts=4 sw=4 et:
// Default to only smarty support for now
// The autoload fails to handle this because its smarty naming

/**
 * The Aether web framework
 *
 * Aether is a rule driven modularized web framework for PHP.
 * Instead of following a more traditional MVC pattern
 * it helps you connect a resource (an url) to a set of modules
 * that each provide a part of the page
 *
 * A "module" can be thought of as a Controller, except you can, and will,
 * have multiple of them for each page as normaly a page requires
 * functionality that doesnt logicaly connect to just one component.
 * Viewing an article? Then you probably need links to more articles,
 * maybe some forum integration, maybe a gallery etc etc
 * All these things should in Aether be handled as separate modules
 * instead of trying to jam it into one controller.
 * 
 * Instead of bringing in a huge package of thousands of thousands of lines of code
 * containing everything from the framework, to models (orm) to templating to helpers
 * Aether focusing merely on the issue of delegating urls/resources to code
 * and in their communication in between.
 * 
 * 
 * Created: 2007-01-31
 * @author Raymond Julin
 * @package aether
 */

class Aether {
    
    /**
     * Hold service locator
     * @var AetherServiceLocator
     */
    private $sl = null;
    
    /**
     * Section
     * @var AetherSection
     */
    private $section = null;
    
    /**
     * Root folder for this project
     * @var string
     */
    private $projectRoot;
    
    /**
     * Module manager
     * The ModuleManager holds a mapping over all modules in the
     * project and offers some functionality for working with them
     * @var AetherModuleManager
     */
    private $moduleManager;

    public static $aetherPath;
    
    /**
     * Start Aether.
     * On start it will parse the projects configuration file,
     * it will try to match the presented http request to a rule
     * in the project configuration and create some overview
     * over which modules it will need to render once
     * a request to render them comes
     *
     * @access public
     * @return Aether
     * @param string $configPath Optional path to the configuration file for the project
     */
    public function __construct($configPath=false) {
        self::$aetherPath = pathinfo(__FILE__, PATHINFO_DIRNAME) . "/";
        spl_autoload_register(array('Aether', 'autoLoad'));
        $this->sl = new AetherServiceLocator;

        $this->sl->set('aetherPath', self::$aetherPath);
        // Initiate all required helper objects
        $parsedUrl = new AetherUrlParser;
        $parsedUrl->parseServerArray($_SERVER);
        $this->sl->set('parsedUrl', $parsedUrl);
        
        // Set autoloader
        // TODO Make this more uesable
        
        /**
         * Find config folder for project
         * By convention the config folder is always placed at
         * $project/config, while using getcwd() MUST return the
         * $project/www/ folder
         */
        $projectPath = preg_replace("/www\/?$/", "", getcwd());
        $this->sl->set("projectRoot", $projectPath);
        $paths = array(
            $configPath,
            $projectPath . 'config/autogenerated.config.xml',
            $projectPath . 'config/aether.config.xml'
        );
        foreach ($paths as $configPath) {
            if (file_exists($configPath))
                break;
        }
        try {
            $config = new AetherConfig($configPath);
            $config->matchUrl($parsedUrl);
            $this->sl->set('aetherConfig', $config);
        }
        catch (AetherMissingFileException $e) {
            /**
             * This means that someone forgot to ensure the config
             * file actually exists
             */
            $msg = "No configuration file for project found: " . $e->getMessage();
            throw new Exception($msg);
        }
        catch (AetherNoUrlRuleMatchException $e) {
            /**
             * This means parsing of configuration file failed
             * by the simple fact that no rules matches
             * the url. This is due to a bad developer
             */
            $msg = "No rule matched url in config file: " . $e->getMessage();
            throw new Exception($msg);
        }
        /**
         * Set up module manager and run the start() stage
         */
        $this->moduleManager = new AetherModuleManager($this->sl);
        $this->moduleManager->start();

        $options = $config->getOptions(array(
            'AetherRunningMode' => 'prod',
            'cache' => 'off'
        ));
        if ($options['cache'] == 'on') {
            if (isset($options['cacheClass']) && isset($options['cacheOptions'])) {
                $cache = $this->getCacheObject($options['cacheClass'], $options['cacheOptions']);
                $this->sl->set("cache", $cache);
            }
        }

        /**
         * Make sure base and root for this request is stored
         * in the service locator so it can be made available
         * to the magical $aether array in templates
         */
        $magic = $this->sl->getVector('templateGlobals');
        $magic['base'] = $config->getBase();
        $magic['root'] = $config->getRoot();
        $magic['urlVars'] = $config->getUrlVars();
        $magic['runningMode'] = $options['AetherRunningMode'];
        $magic['requestUri'] = $_SERVER['REQUEST_URI'];
        $magic['domain'] = $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['HTTP_REFERER']))
            $magic['referer'] = $_SERVER['HTTP_REFERER'];
        if ($_SERVER['SERVER_PORT'] != 80)
            $magic['domain'] .= ":" . $_SERVER['SERVER_PORT'];
        $magic['options'] = $options;

        /**
         * If we are in TEST mode we should prepare a timer object
         * and time everything that happens
         */
        if ($options['AetherRunningMode'] == 'test') {
            // Prepare timer
            $timer = new AetherTimer;
            $timer->start('aether_main');
            $this->sl->set('timer', $timer);
        }

        // Initiate section
        try {
            $searchPath = (isset($options['searchpath'])) 
                ? $options['searchpath'] : $projectPath;
            AetherSectionFactory::$path = $searchPath;
            $this->section = AetherSectionFactory::create(
                $config->getSection(), 
                $this->sl
            );
            $this->sl->set('section', $this->section);
            if (isset($timer)) 
                $timer->tick('aether_main', 'section_initiate');
        }
        catch (Exception $e) {
            // Failed to load section, what to do?
            throw new Exception('Failed horribly: ' . $e->getMessage());
        }
    }
    
    /**
     * Ask the AetherSection to render itself,
     * or if a service is requested it will try to load that service
     *
     * @access public
     * @return string
     */
    public function render() {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        /**
         * If a service is requested simply render the service
         */
        if (isset($_GET['module']) && isset($_GET['service'])) {
            $response = $this->section->service(
                $_GET['module'], $_GET['service']);
            if (!is_object($response) || !($response instanceof AetherResponse)) {
                trigger_error("Expected " . preg_replace("/[^A-z0-9]+/", "", $_GET['module']) . "::service() to return an AetherResponse object", E_USER_WARNING);
            }
            else {
                $response->draw($this->sl);
            }
        }
        else if (isset($_GET['_esi'])) {
            /**
             * ESI support and rendering of only one module by provider name
             * # _esi to list
             * # _esi=<providerName> to render one module with settings of the url path
             */
            if (strlen($_GET['_esi']) > 0) {
                $this->section->renderProviderWithCacheHeaders($_GET['_esi']);
            }
            else {
                $modules = $config->getModules();
                $providers = array();
                foreach ($modules as $m) {
                    $providers[] = array(
                        'provides' => $m['provides'],
                        'cache' => isset($m['cache']) ? $m['cache'] : false
                    );
                }
                $response = new AetherJSONResponse(array('providers' => $providers));
                $response->draw($this->sl);
            }
        }
        else {
            /**
             * Start session if session switch is turned on in 
             * configuration file
             */
            if (array_key_exists('session', $options) 
                    AND $options['session'] == 'on') {
                session_start();
            }

            $response = $this->section->response();
            $response->draw($this->sl);
            /**
             * Run stop stage of modules
             */
            $this->moduleManager->stop();
        }
    }

    /**
     * Used to autoload aether classes
     *
     * @access public
     * @return bool
     */
    public static function autoLoad($class) {
        if (class_exists($class, false))
            return true;
        if ($class == "Smarty")
            require_once(self::$aetherPath . 'lib/templating/smarty/libs/Smarty.class.php');

        // Split up the name of the class by camel case (AetherDriver
        $matches = preg_split('/([A-Z][^A-Z]+)/', $class, -1,
                              PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (empty($matches) || $matches[0] != 'Aether')
            return false;

        // Find the class location
        switch ($matches[1]) {
            case 'Template':
                $path = self::$aetherPath . 'lib/templating/';
                break;
            default:
                $path = self::$aetherPath . 'lib/';
                break;
        }


        $i = 0;
        foreach ($matches as $match) {
            // Turn the rest of the array into a string that can be used as a filename
            $filenameArray = array_slice($matches, $i);
            $filename = implode('', $filenameArray);

            // Check if there is a file with this name.
            // Files have precendence over folders
            $filename = $path . $filename . '.php';
            if (file_exists($filename)) {
                $filePath = $filename;
                break;
            }

            // If there is a directory with this name add it to the dir path
            $match = strtolower($match);
            if (file_exists($path . $match))
                $path = $path . $match . '/';
            else
                break;

            $i++;
        }

        if (isset($filePath) && !empty($filePath)) {
            require $filePath;
            return true;
        }
        
        return false;
    }

    private function getCacheObject($class, $options) {
        if (class_exists($class)) {
            $obj = new $class($options);
            if ($obj instanceof AetherCacheInterface)
                return $obj;
        }
        return false;
    }
}
