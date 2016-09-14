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
    protected $sl;

    /** @var \AetherViewFactory */
    protected $factory;

    /**
     * Constructor.
     *
     * @param \AetherServiceLocator $sl
     */
    public function __construct(AetherServiceLocator $sl)
    {
        $this->sl = $sl;

        // Create a new View Factory instance and bind it to the Service
        // Locator.
        $this->sl->set('view', $this->makeViewFactory());

        // The following will inject the `$aether` variable into all views.
        $this->sl->view()->share('aether', [
            'providers' => $this->sl->getVector('aetherProviders')
        ] + $this->sl->getVector('templateGlobals')->getAsArray());
    }

    /**
     * Make me a View Factory.
     *
     * @return \AetherViewFactory
     */
    protected function makeViewFactory()
    {
        $factory = new AetherViewFactory(
            $this->makeEngineResolver($this->sl),
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
                    $this->sl->get('projectRoot').'.cache/views'
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
            [$this->sl->get('projectRoot').'views']
        );
    }
}
