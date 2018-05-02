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

        $this->aether->singleton('cache', function ($aether) use ($method) {
            return $this->{$method}($aether);
        });
    }

    protected function getMemcacheDriver()
    {
        return new MemcacheDriver(
            config('app.cache.memcache_servers', [])
        );
    }

    protected function getFileDriver($aether)
    {
        return new FileDriver($aether['projectRoot'].'storage/cache', $aether['files']);
    }

    protected function getArrayDriver()
    {
        return new ArrayDriver;
    }
}
