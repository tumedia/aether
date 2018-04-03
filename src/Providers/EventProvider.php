<?php

namespace Aether\Providers;

use Illuminate\Events\Dispatcher;

class EventProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('events', function ($aether) {
            return new Dispatcher($aether);
        });
    }
}
