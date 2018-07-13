<?php

namespace Aether;

use RuntimeException;
use Aether\Sections\Section;
use Aether\Sections\SectionFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;

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
     * @var array
     */
    private $registeredProviders = [];

    /**
     * @var string
     */
    private $namespace;

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

        $this->registerBaseProviders();

        $this->registerCoreContainerAliases();
    }

    public function bootstrapWith(array $bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Generate a HTTP response throught the HTTP kernel, then send the
     * response to the browser.
     *
     * @return void
     */
    public function render()
    {
        $kernel = $this->make(Http\Kernel::class);

        $response = $kernel->handle(UrlParser::createFromGlobals());

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
     * Check if we're running in a command-line environment.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $projectRoot = realpath($this['projectRoot']);

        $composer = json_decode(file_get_contents($projectRoot.'/composer.json'));

        foreach (data_get($composer, 'autoload.psr-4', []) as $namespace => $path) {
            if (realpath($projectRoot.'/'.$path) === $projectRoot.'/src') {
                return $this->namespace = $namespace;
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
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
     * Call the boot method on all services that have been registered.
     *
     * @return void
     */
    public function bootProviders()
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
    public function initiateSection()
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

    /**
     * Set up some important core bindings in the container.
     *
     * @return void
     */
    private function setUpBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->singleton(ExceptionHandler::class, Exceptions\Handler::class);
    }

    private function registerBaseProviders()
    {
        $this->register(Providers\ConfigProvider::class);

        $this->register(Providers\EventsProvider::class);

        $this->register(PackageDiscovery\PackageDiscoveryProvider::class);

        $this->register(Providers\SentryProvider::class);
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

    private function registerCoreContainerAliases()
    {
        foreach ([
            'app'          => [\Aether\Aether::class, \Aether\ServiceLocator::class, \Illuminate\Container\Container::class, \Illuminate\Contracts\Container\Container::class, \Psr\Container\ContainerInterface::class],
            'aetherConfig' => [\Aether\AetherConfig::class],
            'cache'        => [\Aether\Cache\Cache::class],
            'config'       => [\Aether\Config::class, \Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'db'           => [\Illuminate\Database\DatabaseManager::class],
            'events'       => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
            'files'        => [\Illuminate\Filesystem\Filesystem::class],
            'template'     => [\Aether\Templating\Template::class],
            'timer'        => [\Aether\Timer::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
