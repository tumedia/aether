<?php

namespace Aether\Services;

use Aether\Timer;

class TimerService extends Service
{
    public function register()
    {
        if (!in_array(config('app.env'), ['local', 'development'])) {
            return;
        }

        // If we are in local (development) mode we should prepare a timer
        // object and time everything that happens.

        $this->container->instance('timer', $timer = new Timer);

        $timer->start('aether_main');
    }
}
