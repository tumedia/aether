<?php

namespace Aether\Session;

use Aether\Cache\Cache;
use Aether\Providers\Provider;

class SessionProvider extends Provider
{
    public function boot(Cache $cache)
    {
        session_set_save_handler(new CacheSessionHandler($cache));
    }
}
