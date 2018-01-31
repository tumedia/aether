<?php

namespace Tests\Cache;

use AetherCacheFiles;
use Tests\Traits\FileCache;
use PHPUnit\Framework\TestCase;

class FilesCacheTest extends TestCase
{
    use FileCache, CacheTestsTrait;

    protected function setUp()
    {
        $this->cache = $this->setUpFileCache();
    }

    protected function tearDown()
    {
        $this->tearDownFileCache();
    }
}
