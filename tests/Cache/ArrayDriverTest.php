<?php

namespace Tests\Cache;

use Aether\Cache\ArrayDriver;
use PHPUnit\Framework\TestCase;

class ArrayDriverTest extends TestCase
{
    use CacheTestsTrait;

    protected function setUp()
    {
        $this->cache = new ArrayDriver;
    }
}
