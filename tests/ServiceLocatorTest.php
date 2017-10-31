<?php

namespace Tests;

use AetherServiceLocator;
use PHPUnit\Framework\TestCase;

class ServiceLocatorTest extends TestCase
{
    public function testCustomObjectStorage()
    {
        $object = (object)[
            'foo' => 'bar',
        ];

        $sl = new AetherServiceLocator;
        $sl->set('tester', $object);

        $this->assertSame($object, $sl->get('tester'));
    }

    public function testArray()
    {
        $sl = new AetherServiceLocator;

        $arr = $sl->getVector('foo');
        $arr['foo'] = 'bar';

        $arr2 = $sl->getVector('foo');

        $this->assertEquals($arr['foo'], $arr2['foo']);
    }
}
