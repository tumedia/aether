<?php

namespace Aether\Services;

use Aether\Config;
use Aether\ServiceLocator;

abstract class Service
{
    /**
     * The service container instance.
     *
     * @var \Aether\ServiceLocator
     */
    protected $container;

    public function __construct(ServiceLocator $container)
    {
        $this->container = $container;
    }

    /**
     * Register the service.
     *
     * @return void
     */
    abstract public function register();

    /**
     * dsfsd
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function loadConfigFrom($path, $key)
    {
        $config = $this->container['config'];

        if ($config->wasLoadedFromCompiled()) {
            return;
        }

        $localConfig = $config->get($key, []);

        // todo: figure out if this is a good merging strategy
        $config->set($key, array_replace_recursive(require $path, $localConfig));
    }
}
