<?php

namespace Aether\Session;

use Aether\Services\Service;

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
        return $this->container['cache'];
    }
}
