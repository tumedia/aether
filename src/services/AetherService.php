<?php

abstract class AetherService
{
    protected $sl;

    public function __construct(AetherServiceLocator $sl)
    {
        $this->sl = $sl;
    }

    abstract public function register();
}
