<?php

use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;

class AetherServiceWhoops extends AetherService
{
    public function register()
    {
        if (config('app.env', '') !== 'local') {
            return;
        }

        $whoops = new Whoops;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->register();
    }
}
