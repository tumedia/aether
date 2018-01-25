<?php

namespace Aether\Services;

use Aether\Cache\Cache;
use Aether\Cache\FileDriver;
use Aether\Cache\ArrayDriver;
use InvalidArgumentException;
use Aether\Cache\MemcacheDriver;

class CacheService extends Service
{
    public function register()
    {
        $driver = config('app.cache.driver', 'memcache');

        $method = 'get'.ucfirst($driver).'Driver';

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Cache driver [{$driver}] is not supported");
        }

        $this->container->singleton(Cache::class, function () use ($method) {
            return $this->{$method}();
        });

        // Backwards compatibility...
        $this->container->alias(Cache::class, 'cache');
    }

    protected function getMemcacheDriver()
    {
        return new MemcacheDriver(
            config('app.cache.memcache_servers', [])
        );
    }

    protected function getFileDriver()
    {
        return new FileDriver($this->container['projectRoot'].'storage/cache');
    }

    protected function getArrayDriver()
    {
        return new ArrayDriver;
    }
}
