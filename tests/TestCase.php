<?php

namespace Tests;

use Aether\Aether;
use Aether\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createAether()
    {
        return new Aether(__DIR__.'/Fixtures');
    }
}
