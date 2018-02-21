<?php

namespace Aether\Services;

use Illuminate\Events\Dispatcher;

class EventService extends Service
{
    public function register()
    {
        $this->container->singleton('events', function ($container) {
            return new Dispatcher($container);
        });
    }
}
