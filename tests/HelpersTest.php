<?php

namespace Tests;

class HelpersTest extends TestCase
{
    public function testAppMethodReturnsAether()
    {
        $this->assertSame($this->aether, \app());
    }

    public function testAppMethodResolvesAbstract()
    {
        \app()->bind('test.random.number', function ($app, $parameters) {
            return $parameters ? $parameters[0] : 10;
        });

        $this->assertEquals(10, \app('test.random.number'));

        $this->assertEquals(50, \app('test.random.number', [50]));
    }

    public function testResolveMethod()
    {
        \app()->bind('test.dummy-text', function () {
            return 'lorem ipsum';
        });

        $this->assertSame('lorem ipsum', \resolve('test.dummy-text'));
    }

    public function testEventMethod()
    {
        $wasCalled = false;
        resolve('events')->listen('test.event', function () use (&$wasCalled) {
            $wasCalled = true;
        });

        \event('test.event');

        $this->assertTrue($wasCalled);
    }
}
