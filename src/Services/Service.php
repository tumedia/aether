<?php

namespace Aether\Services;

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
}
