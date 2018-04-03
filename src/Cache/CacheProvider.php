<?php

namespace Aether\Cache;

use InvalidArgumentException;
use Aether\Providers\Provider;

class CacheProvider extends Provider
{
    public function register()
    {
        $driver = config('app.cache.driver', 'memcache');

        $method = 'get'.ucfirst($driver).'Driver';

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Cache driver [{$driver}] is not supported");
        }

        $this->aether->singleton('cache', function () use ($method) {
            return $this->{$method}();
        });
    }

    protected function getMemcacheDriver()
    {
        return new MemcacheDriver(
            config('app.cache.memcache_servers', [])
        );
    }

    protected function getFileDriver()
    {
        return new FileDriver($this->aether['projectRoot'].'storage/cache');
    }

    protected function getArrayDriver()
    {
        return new ArrayDriver;
    }
}
