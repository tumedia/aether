<?php

namespace Tests;

use Aether\Aether;

class HelpersTest extends TestCase
{
    public function testAppReturnsAether()
    {
        $this->assertSame($this->aether, \app());
    }

    public function testAppResolvesAbstract()
    {
        \app()->bind('test.random.number', function ($app, $parameters) {
            return $parameters ? $parameters[0] : 10;
        });

        $this->assertEquals(10, \app('test.random.number'));

        $this->assertEquals(50, \app('test.random.number', [50]));
    }

    public function testResolve()
    {
        \app()->bind('test.dummy-text', function () {
            return 'lorem ipsum';
        });

        $this->assertSame('lorem ipsum', \resolve('test.dummy-text'));
    }
}
