<?php

namespace Aether\Providers;

use Aether\Timer;

class TimerProvider extends Provider
{
    public function boot()
    {
        if (! in_array(config('app.env'), ['local', 'development'])) {
            return;
        }

        // If we are in local (development) mode we should prepare a timer
        // object and time everything that happens.

        $this->aether->instance('timer', $timer = new Timer);

        $timer->start('aether_main');
    }
}
