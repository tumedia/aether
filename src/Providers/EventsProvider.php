<?php

namespace Aether\Providers;

use Illuminate\Events\Dispatcher;

class EventsProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('events', function ($aether) {
            return new Dispatcher($aether);
        });
    }

    public function boot(Dispatcher $events)
    {
        foreach ($this->getConfiguredListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->getConfiguredSubscribers() as $subscriber) {
            $events->subscribe($subscriber);
        }
    }

    protected function getConfiguredListeners()
    {
        return config('events.listen', []);
    }

    protected function getConfiguredSubscribers()
    {
        return config('events.subscribe', []);
    }
}
