<?php

class AetherServiceTimer extends AetherService
{
    public function register()
    {
        if (!in_array(config('app.env'), ['local', 'development'])) {
            return;
        }

        // If we are in local (development) mode we should prepare a timer
        // object and time everything that happens.

        $timer = new AetherTimer;

        $timer->start('aether_main');

        $this->sl->set('timer', $timer);
    }
}
