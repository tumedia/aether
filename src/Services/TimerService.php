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

        $this->container->instance(Timer::class, $timer = new Timer);

        // Backward compatibility...
        $this->container->alias(Timer::class, 'timer');

        $timer->start('aether_main');
    }
}
