<?php

namespace Aether;

use Aether\Sections\Section;
use Aether\Sections\SectionFactory;
use Aether\Response\ResponseFactory;

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
class Aether extends ServiceLocator
{
    /**
     * Service classes that should be registered when Aether boots. Keep in mind
     * that the order in which the services are registered highly matters, as
     * one service may depend on a service that should be registered before it.
     *
     * @var array
     */
    private $coreServices = [
        Services\ConfigService::class,
        Services\WhoopsService::class,
        Services\LocalizationService::class,
        Services\SentryService::class,
        Services\EventService::class,
        Cache\CacheService::class,
        Session\SessionService::class,
        Templating\TemplateService::class,
        Services\TimerService::class,
        Services\DatabaseService::class,
        PackageDiscovery\PackageDiscoveryService::class,
    ];

    /**
     * @var array
     */
    private $bootedCallbacks = [];

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
        $this->instance('projectRoot', rtrim($projectRoot, '/').'/');

        $this->setUpBaseBindings();

        $this->registerCoreServices();

        $this->registerCoreContainerAliases();

        $this->registerAppServices();

        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }
    }

    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;
    }

    /**
     * Ask the AetherSection to render itself,
     * or if a service is requested it will try to load that service
     *
     * @return void
     */
    public function render()
    {
        $this->initiateSection();

        $response = $this->call([ResponseFactory::createFromGlobals(), 'getResponse']);

        $response->draw($this);
    }

    /**
     * Determine if Aether is running in a production environment.
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this['config']['app.env'] === 'production';
    }

    /**
     * Set up some important core bindings in the container.
     *
     * @return void
     */
    private function setUpBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        if (! $this->runningInConsole()) {
            $this->singleton('parsedUrl', function ($container) {
                return UrlParser::createFromGlobals();
            });
        }
    }

    /**
     * Check if we're running in a command-line environment.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli';
    }

    private function registerCoreServices()
    {
        foreach ($this->coreServices as $service) {
            $this->register($service);
        }
    }

    private function registerAppServices()
    {
        foreach ($this['config']->get('app.services') as $service) {
            $this->register($service);
        }
    }

    private function register($serviceClass)
    {
        (new $serviceClass($this))->register();
    }

    /**
     * Initiate the requested section and register it with the service locator.
     *
     * @return void
     */
    private function initiateSection()
    {
        $this->instance('section', SectionFactory::create(
            $this['aetherConfig']->getSection(),
            $this
        ));

        $this->alias('section', Section::class);

        if ($this->bound('timer')) {
            $this['timer']->tick('aether_main', 'section_initiate');
        }
    }

    protected function registerCoreContainerAliases()
    {
        foreach ([
            'app'          => [\Aether\Aether::class, \Aether\ServiceLocator::class, \Illuminate\Container\Container::class, \Illuminate\Contracts\Container\Container::class, \Psr\Container\ContainerInterface::class],
            'aetherConfig' => [\Aether\AetherConfig::class],
            'cache'        => [\Aether\Cache\Cache::class],
            'config'       => [\Aether\Config::class, \Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'db'           => [\Illuminate\Database\DatabaseManager::class],
            'events'       => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
            'template'     => [\Aether\Templating\Template::class],
            'timer'        => [\Aether\Timer::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
