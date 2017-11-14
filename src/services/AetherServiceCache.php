<?php

class AetherServiceCache extends AetherService
{
    public function register()
    {
        $driver = config('app.cache.driver', 'memcache');

        $method = 'get'.ucfirst($driver).'Driver';

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Cache driver [{$driver}] is not supported");
        }

        $this->sl->set('cache', $this->{$method}());
    }

    protected function getMemcacheDriver()
    {
        return new AetherCacheMemcache(
            config('app.cache.memcache_servers', $this->getDefaultMemcacheServers())
        );
    }

    protected function getFileDriver()
    {
        return new AetherCacheFile($this->sl->get('projectRoot').'storage/cache');
    }

    /**
     * Get the default Memcache servers. This is provided for backwards compatiblity.
     *
     * @todo This can be removed once all sites have defined
     *       `config('app.cache.memcache_servers')`
     *
     * @return array
     */
    protected function getDefaultMemcacheServers()
    {
        return [
            'auto.tu.c.bitbit.net',
            'boss.tu.c.bitbit.net',
            'kaos.tu.c.bitbit.net',
            'karr.tu.c.bitbit.net',
            'nell.tu.c.bitbit.net',
            'wopr.tu.c.bitbit.net',
        ];
    }
}
