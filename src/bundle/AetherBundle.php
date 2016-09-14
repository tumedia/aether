<?php

abstract class AetherBundle
{
    /**
     * The name of the bundle.
     *
     * @var string
     */
    public $name;

    /** @var \AetherServiceLocator */
    protected $sl;

    /**
     * Constructor.
     *
     * @param  \AetherServiceLocator $sl
     */
    public function __construct(AetherServiceLocator $sl)
    {
        $this->sl = $sl;
    }

    /**
     * Bootstrap the bundle. If the bootstrapper returns false, the bundle
     * instance will not get bound to the Bundle Manager.
     *
     * @return bool|null
     */
    public function bootstrap()
    {
        return false;
    }

    /**
     * Register a path to load views from.
     *
     * @param  string  $path
     * @return void
     */
    protected function loadViewsFrom(string $path)
    {
        $this->sl->view()->addNamespace($this->name, $path);
    }
}
