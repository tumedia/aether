<?php

namespace Aether\Services;

use Aether\ServiceLocator;

abstract class Service
{
    protected $sl;

    public function __construct(ServiceLocator $sl)
    {
        $this->sl = $sl;
    }

    abstract public function register();
}
