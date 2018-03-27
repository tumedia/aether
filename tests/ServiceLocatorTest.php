<?php

namespace Tests;

use AetherTemplate;
use AetherServiceLocator;

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

    public function testReturnNullIfObjectDoesNotExist()
    {
        $this->assertNull((new AetherServiceLocator)->get('foo'));
    }

    public function testOverwriteIfObjectAlreadyExists()
    {
        $sl = new AetherServiceLocator;

        $sl->set('foo', 'bar');
        $sl->set('foo', 'updated');

        $this->assertSame('updated', $sl->get('foo'));
    }

    public function testHasObjectMethod()
    {
        $sl = new AetherServiceLocator;

        $this->assertFalse($sl->has('foo'));
        $this->assertFalse($sl->hasObject('foo'));

        $sl->set('foo', 'bar');

        $this->assertTrue($sl->has('foo'));
        $this->assertTrue($sl->hasObject('foo'));
    }

    public function testArray()
    {
        $sl = new AetherServiceLocator;

        $arr = $sl->getVector('foo');
        $arr['foo'] = 'bar';

        $arr2 = $sl->getVector('foo');

        $this->assertSame($arr, $arr2);
    }

    public function testGetTemplate()
    {
        $this->assertInstanceOf(
            AetherTemplate::class,
            $this->aether->getTemplate()
        );
    }
}
