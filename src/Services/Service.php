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
     * Load base configuration from a given file. If the local app config
     * already contains data under the given key, this method will not
     * overwrite any existing values, it will simply fill in the blanks.
     * Perfect for a package to load its base config.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function fillConfigFrom($path, $key)
    {
        $config = $this->container['config'];

        if ($config->wasLoadedFromCompiled()) {
            return;
        }

        $localConfig = $config->get($key, []);

        $config->set($key, array_replace_recursive(require $path, $localConfig));
    }
}
