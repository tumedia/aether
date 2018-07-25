<?php

namespace Tests\Events;

use Aether\Aether;
use PHPUnit\Framework\TestCase;
use Aether\Console\Kernel as ConsoleKernel;

class EventsProviderTest extends TestCase
{
    protected function tearDown()
    {
        unset($_SERVER['__event.test']);

        Aether::setInstance(null);
    }

    public function testThatItListensForConfiguredEvents()
    {
        $aether = new Aether;
        $aether['config']->set('events.listen', [FooEvent::class => [FooHandler::class]]);
        $aether->make(ConsoleKernel::class)->bootstrap();

        $aether['events']->dispatch(new FooEvent);

        $this->assertEquals('triggered', $_SERVER['__event.test']);
    }

    public function testThatIsRegistersSubscribers()
    {
        $aether = new Aether;
        $aether['config']->set('events.subscribe', [FooSubscriber::class]);
        $aether->make(ConsoleKernel::class)->bootstrap();

        $aether['events']->dispatch(new FooEvent);

        $this->assertEquals('triggered', $_SERVER['__event.test']);
    }
}
