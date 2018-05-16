<?php

namespace Tests\Events;

use Illuminate\Contracts\Events\Dispatcher;

class FooSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(FooEvent::class, FooHandler::class);
    }
}
