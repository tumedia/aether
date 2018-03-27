<?php

namespace Tests\Cache;

use Aether\Cache\ArrayDriver;

class ArrayDriverTest extends AbstractCacheTest
{
    protected function setUp()
    {
        $this->cache = new ArrayDriver;
    }
}
