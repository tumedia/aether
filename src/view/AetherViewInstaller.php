<?php

use Illuminate\View\{
    FileViewFinder,
    Engines\PhpEngine,
    Engines\CompilerEngine,
    Engines\EngineResolver,
    Compilers\BladeCompiler
};

use Illuminate\Events\Dispatcher;

/**
 * :wat:
 */
class AetherViewInstaller
{
    /** @var \AetherServiceLocator */
    protected static $sl;

    /** @var array */
    protected static $paths = [];

    /** @var \AetherViewFactory */
    protected $factory;

    /**
     * "Install" a View Factory onto the Aether Service Locator.
     *
     * @param \AetherServiceLocator $sl
     */
    public static function install(AetherServiceLocator $sl)
    {
        static::$sl = $sl;

        // Add app view paths.
        static::addPath(static::$sl->get('projectRoot').'resources/views');
        static::addPath(static::$sl->get('projectRoot').'templates');

        $installer = new static;

        // Create a new View Factory instance and bind it to the Service
        // Locator.
        static::$sl->set('view', $installer->makeViewFactory(static::$sl));

        // The following will inject the `$aether` variable into all views.
        static::$sl->view()->share('aether', [
            'providers' => static::$sl->getVector('aetherProviders')
        ] + static::$sl->getVector('templateGlobals')->getAsArray());
    }

    /**
     * Get registered paths.
     *
     * @return array
     */
    public static function getPaths()
    {
        return static::$paths;
    }

    /**
     * Add a path to the thing.
     *
     * @param  string $path
     * @return void
     */
    public static function addPath($path)
    {
        static::$paths[] = $path;
    }

    /**
     * Get the cache path.
     *
     * @return string
     */
    public static function getCachePath()
    {
        return static::$sl->get('projectRoot').'.cache/views';
    }

    /**
     * Make me a View Factory.
     *
     * @param \AetherServiceLocator $sl
     * @return \AetherViewFactory
     */
    protected function makeViewFactory(AetherServiceLocator $sl)
    {
        $factory = new AetherViewFactory(
            $this->makeEngineResolver($sl),
            $this->makeViewFinder(),
            new Dispatcher
        );

        $factory->addExtension('tpl', 'tpl');

        return $factory;
    }

    /**
     * @param  \AetherServiceLocator $sl
     * @return \Illuminate\View\Engines\EngineResolver
     */
    protected function makeEngineResolver(AetherServiceLocator $sl)
    {
        $resolver = new EngineResolver;

        $resolver->register('php', function () {
            return new PhpEngine;
        });

        $resolver->register('blade', function () {
            return new CompilerEngine(
                new BladeCompiler(
                    new Illuminate\Filesystem\Filesystem,
                    static::getCachePath()
                )
            );
        });

        $resolver->register('tpl', function () use ($sl) {
            return new AetherSmartyEngine($sl);
        });

        return $resolver;
    }

    /**
     * @return \Illuminate\View\FileViewFinder
     */
    protected function makeViewFinder()
    {
        return new FileViewFinder(
            new Illuminate\Filesystem\Filesystem,
            static::$paths
        );
    }
}
