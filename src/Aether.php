<?php

namespace Aether;

use Aether\Response\Json;
use Aether\Response\Response;
use Aether\Sections\SectionFactory;

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
class Aether
{
    /** @var \Aether */
    protected static $globalInstance;

    /**
     * Hold service locator
     * @var \Aether\ServiceLocator
     */
    private $sl = null;

    /**
     * Root folder for this project
     * @var string
     */
    private $projectRoot;

    /**
     * Service classes that should be registered when Aether boots. Keep in mind
     * that the order in which the services are registered highly matters, as
     * one service may depend on a service that should be registered before it.
     *
     * @var array
     */
    private $services = [
        Services\ConfigService::class,
        Services\WhoopsService::class,
        Services\SentryService::class,
        Services\CacheService::class,
        Services\SessionService::class,
        Services\TemplateGlobalsService::class,
        Services\TimerService::class,
    ];

    /**
     * Get the global Aether instance.
     *
     * @return \Aether\Aether
     */
    public static function getInstance()
    {
        return static::$globalInstance;
    }

    /**
     * Set the global Aether instance.
     *
     * @param  \Aether\Aether|null $aether
     * @return void
     */
    public static function setInstance(Aether $aether = null)
    {
        static::$globalInstance = $aether;
    }

    /**
     * Start Aether.
     * On start it will parse the projects configuration file,
     * it will try to match the presented http request to a rule
     * in the project configuration and create some overview
     * over which modules it will need to render once
     * a request to render them comes
     *
     * @param  string|null  $projectRoot  The base path to the project.
     * @return Aether
     */
    public function __construct($projectRoot = null)
    {
        static::setInstance($this);
        $this->sl = new ServiceLocator;

        // Initiate all required helper objects
        $parsedUrl = new UrlParser;
        $parsedUrl->parseServerArray($_SERVER);
        $this->sl->set('parsedUrl', $parsedUrl);

        $this->sl->set('projectRoot', rtrim($projectRoot, '/').'/');

        $this->registerServices();

        $this->initiateSection();

        if ($this->sl->has('timer')) {
            $this->sl->get('timer')->tick('aether_main', 'section_initiate');
        }
    }

    /**
     * Ask the AetherSection to render itself,
     * or if a service is requested it will try to load that service
     *
     * @access public
     * @return string
     */
    public function render()
    {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        $section = $this->sl->get('section');

        /**
         * If a service is requested simply render the service
         */
        if (isset($_GET['module']) && isset($_GET['service'])) {
            $response = $section->service($_GET['module'], $_GET['service']);
            if (!is_object($response) || !($response instanceof Response)) {
                trigger_error("Expected " . preg_replace("/[^A-z0-9]+/", "", $_GET['module']) . "::service() to return an AetherResponse object." . (isset($_SERVER['HTTP_REFERER']) ? " Referer: " . $_SERVER['HTTP_REFERER'] : ""), E_USER_WARNING);
            } else {
                $response->draw($this->sl);
            }
        } elseif (isset($_GET['_esi'])) {
            /**
             * ESI support and rendering of only one module by provider name
             * # _esi to list
             * # _esi=<providerName> to render one module with settings of the url path
             */
            if (strlen($_GET['_esi']) > 0) {
                $locale = (isset($options['locale'])) ? $options['locale'] : "nb_NO.UTF-8";
                setlocale(LC_ALL, $locale);

                $lc_numeric = (isset($options['lc_numeric'])) ? $options['lc_numeric'] : 'C';
                setlocale(LC_NUMERIC, $lc_numeric);

                if (isset($options['lc_messages'])) {
                    $localeDomain = "messages";
                    setlocale(LC_MESSAGES, $options['lc_messages']);
                    bindtextdomain($localeDomain, __DIR__ . "/locales");
                    bind_textdomain_codeset($localeDomain, 'UTF-8');
                    textdomain($localeDomain);
                }
                $section->renderProviderWithCacheHeaders($_GET['_esi']);
            } else {
                $modules = $config->getModules();
                $providers = array();
                foreach ($modules as $m) {
                    $provider = [
                        'provides' => isset($m['provides']) ? $m['provides'] : null,
                        'cache' => isset($m['cache']) ? $m['cache'] : false
                    ];
                    if (isset($m['module'])) {
                        $provider['providers'] = array_map(function ($m) {
                            return [
                                'provides' => $m['provides'],
                                'cache' => isset($m['cache']) ? $m['cache'] : false
                            ];
                        }, array_values($m['module']));
                    }
                    $providers[] = $provider;
                }
                $response = new Json(compact('providers'));
                $response->draw($this->sl);
            }
        } else {
            /**
             * Start session if session switch is turned on in
             * configuration file
             */
            if (array_key_exists('session', $options)
                    and $options['session'] == 'on') {
                session_start();
            }

            $response = $section->response();
            $response->draw($this->sl);
        }
    }

    /**
     * Get the AetherServiceLocator instance.
     *
     * @return \Aether\ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->sl;
    }

    /**
     * Set the AetherServiceLocator instance.
     *
     * @param  \Aether\ServiceLocator  $sl
     * @return void
     */
    public function setServiceLocator($sl)
    {
        $this->sl = $sl;
    }

    /**
     * Register services.
     *
     * @return void
     */
    private function registerServices()
    {
        foreach ($this->services as $service) {
            (new $service($this->getServiceLocator()))->register();
        }
    }

    /**
     * Initiate the requested section and register it with the service locator.
     *
     * @return void
     */
    private function initiateSection()
    {
        $this->sl->set('section', SectionFactory::create(
            $this->sl->get('aetherConfig')->getSection(),
            $this->sl
        ));
    }
}
