<?php

namespace Tests;

use Aether\Vector;
use PHPUnit\Framework\TestCase;

class VectorTest extends TestCase
{
    public function testGetAsArray()
    {
        $vector = new Vector('foo', 'bar');

        $this->assertSame(['foo', 'bar'], $vector->getAsArray());
    }

    public function testContains()
    {
        $vector = new Vector('foo', 'bar');

        $this->assertTrue($vector->contains('foo'));
        $this->assertTrue($vector->contains('bar'));
        $this->assertFalse($vector->contains('baz'));
    }

    public function testAppend()
    {
        $vector = new Vector;

        $this->assertFalse($vector->contains('foo'));

        $vector->append('foo');

        $this->assertTrue($vector->contains('foo'));
    }

    public function testArrayAccess()
    {
        $vector = new Vector('foo');

        $this->assertArraySubset(['foo'], $vector);

        $this->assertArrayHasKey(0, $vector);
        $this->assertArrayNotHasKey(1, $vector);

        $vector[0] = 'bar';
        $vector[1] = 'baz';
        $vector[] = 'qux';

        $this->assertSame(['bar', 'baz', 'qux'], $vector->getAsArray());

        unset($vector[2]);

        $this->assertSame(['bar', 'baz'], $vector->getAsArray());

        $this->assertSame('bar', $vector[0]);
        $this->assertSame('baz', $vector[1]);
    }

    public function testIteration()
    {
        $vector = new Vector('foo', 'bar');

        $this->assertSame(['foo', 'bar'], iterator_to_array($vector));
    }
}
