<?php

namespace Tests\Cache;

use Tests\Traits\FileCache;

class FilesCacheTest extends AbstractCacheTest
{
    use FileCache;

    protected function setUp()
    {
        $this->cache = $this->setUpFileCache();
    }

    protected function tearDown()
    {
        $this->tearDownFileCache();
    }
}
