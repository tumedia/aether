<?php

namespace Aether\Providers;

use Whoops\Util\Misc;
use Whoops\Run as Whoops;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class WhoopsProvider extends Provider
{
    public function boot()
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
        if ($this->aether->runningInConsole()) {
            return new PlainTextHandler;
        }

        if (Misc::isAjaxRequest()) {
            return (new JsonResponseHandler)->addTraceToOutput(true);
        }

        return new PrettyPageHandler;
    }
}
