<?php

namespace Tests;

use Aether\Aether;

class AetherTest extends TestCase
{
    public function testHasInstance()
    {
        // Because of the TestCase setup, we whould at this point have an
        // Aether instance ready to go.

        $this->assertTrue(Aether::hasInstance());

        Aether::setInstance(null);

        $this->assertFalse(Aether::hasInstance());
    }

    public function testGetNamespace()
    {
        $this->assertEquals('Tests\\Fixtures\\', $this->aether->getNamespace());
    }
}
