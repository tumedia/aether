<?php

use Whoops\Util\Misc;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class AetherServiceWhoops extends AetherService
{
    public function register()
    {
        if (config('app.env', '') !== 'local') {
            return;
        }

        $whoops = new Whoops;

        $whoops->pushHandler($this->getHandler());

        $whoops->register();
    }

    protected function getHandler()
    {
        if (Misc::isAjaxRequest()) {
            return (new JsonResponseHandler)->addTraceToOutput(true);
        }

        return new PrettyPageHandler;
    }
}
