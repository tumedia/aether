<?php

namespace Aether\Services;

use Aether\Cache\Cache;
use Aether\Session\CacheSessionHandler;

class SessionService extends Service
{
    public function register()
    {
        session_set_save_handler(
            new CacheSessionHandler($this->getCache())
        );
    }

    protected function getCache()
    {
        return $this->container->make(Cache::class);
    }
}
