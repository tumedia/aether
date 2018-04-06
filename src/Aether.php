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
     * Service providers that should be registered when Aether boots.
     *
     * @var array
     */
    private $coreProviders = [
        Providers\ConfigProvider::class,
        Providers\WhoopsProvider::class,
        Providers\LocalizationProvider::class,
        Providers\SentryProvider::class,
        Providers\EventProvider::class,
        Cache\CacheProvider::class,
        Session\SessionProvider::class,
        Templating\TemplateProvider::class,
        Providers\TimerProvider::class,
        Providers\DatabaseProvider::class,
        Console\AetherCliProvider::class,
        PackageDiscovery\PackageDiscoveryProvider::class,
    ];

    /**
     * @var array
     */
    private $registeredProviders = [];

    /**
     * Determine if a static Aether instance has been instantiated and
     * registered.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return ! is_null(static::$instance);
    }

    /**
     * Create a new Aether instance.
     *
     * @param  string|null  $projectRoot  The application's root directory.
     */
    public function __construct($projectRoot = null)
    {
        $this->instance('projectRoot', rtrim($projectRoot, '/').'/');

        $this->setUpBaseBindings();

        $this->registerProviders($this->coreProviders);

        $this->registerCoreContainerAliases();

        $this->bootProviders();
    }

    /**
     * Ask the AetherSection to render itself,
     * or if a service is requested it will try to load that service.
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

    /**
     * Register an array of service providers.
     *
     * @param  string[]  $providers
     * @return void
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Instantiate and register a given service provider, and add it to the
     * registered providers array.
     *
     * @param  string  $provider
     * @return void
     */
    private function register($provider)
    {
        $this->registeredProviders[$provider] = tap(new $provider($this))->register();
    }

    /**
     * Call the boot method on all services that have been registered.
     *
     * @return void
     */
    private function bootProviders()
    {
        foreach ($this->registeredProviders as $provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        }

        $this->registeredProviders = [];
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
