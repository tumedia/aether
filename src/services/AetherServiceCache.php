<?php

class AetherServiceCache extends AetherService
{
    public function register()
    {
        if (!config('app.cache.enabled', true)) {
            return;
        }

        $this->sl->set('cache', $this->getCacheObject(
            config('app.cache.class', AetherCacheMemcache::class),
            config('app.cache.options', $this->getDefaultCacheOptions())
        ));
    }

    protected function getCacheObject($class, $options)
    {
        return new $class($options);
    }

    /**
     * Get the default cache options. This is provided for backwards compatiblity.
     *
     * @todo This can be removed once all sites have cache options defined in
     *       `config('app.cache.options')`
     *
     * @return array
     */
    protected function getDefaultCacheOptions()
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
