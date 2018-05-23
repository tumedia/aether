<?php

namespace Tests\Events;

class FooHandler
{
    public function handle()
    {
        $_SERVER['__event.test'] = 'triggered';
    }
}
