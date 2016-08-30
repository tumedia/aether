<?php

use Illuminate\View\{
    FileViewFinder,
    Factory as ViewFactory,
    Engines\PhpEngine,
    Engines\CompilerEngine,
    Engines\EngineResolver,
    Compilers\BladeCompiler
};

use Illuminate\Events\Dispatcher;

/**
 * Laravel Blade in Aether.
 *
 * @author Kristoffer Alfheim <kristoffer@tu.no>
 */
class AetherBladeInstaller
{
    /** @var \Illuminate\View\Factory */
    protected $factory;

    /**
     * "Install" a Blade Factory on the Aether Service Locator.
     *
     * @param \AetherServiceLocator $sl
     */
    public static function install(AetherServiceLocator $sl)
    {
        $installer = new static;

        $sl->set('view', $installer->makeViewFactory());
    }

    /**
     * Make me a View Factory.
     *
     * @return \Illuminate\View\Factory
     */
    protected function makeViewFactory()
    {
        return new ViewFactory(
            $this->makeEngineResolver(),
            $this->makeViewFinder(),
            new Dispatcher
        );
    }

    /**
     * @return \Illuminate\View\Engines\EngineResolver
     */
    protected function makeEngineResolver()
    {
        $resolver = new EngineResolver;

        $resolver->register('php', function () {
            return new PhpEngine;
        });

        $resolver->register('blade', function () {
            return new CompilerEngine(
                new BladeCompiler(
                    new Illuminate\Filesystem\Filesystem,
                    '/Users/kristoffer/Code/aether-site/cache' // @todo
                )
            );
        });

        return $resolver;
    }

    /**
     * @return \Illuminate\View\FileViewFinder
     */
    protected function makeViewFinder()
    {
        $paths = [
            '/Users/kristoffer/Code/aether-site/resources/views', // @todo
        ];

        return new FileViewFinder(
            new Illuminate\Filesystem\Filesystem,
            $paths
        );
    }
}
