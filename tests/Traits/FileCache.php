<?php

namespace Tests\Traits;

use AetherCacheFile;
use Illuminate\Filesystem\Filesystem;

trait FileCache
{
    protected function setUpFileCache()
    {
        return new AetherCacheFile($this->getStoragePath(), new Filesystem);
    }

    protected function tearDownFileCache()
    {
        array_map('unlink', glob($this->getStoragePath().'/*/*/*'));

        array_map('rmdir', glob($this->getStoragePath().'/*/*', GLOB_ONLYDIR));
        array_map('rmdir', glob($this->getStoragePath().'/*', GLOB_ONLYDIR));
    }

    private function getStoragePath()
    {
        return dirname(__DIR__).'/Fixtures/storage/cache';
    }
}
