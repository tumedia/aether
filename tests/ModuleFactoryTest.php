<?php

namespace Tests;

use AetherModuleFactory;
use AetherServiceLocator;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Modules\Hellolocal;

class ModuleFactoryTest extends TestCase
{
    public function testCreate()
    {
        $mod = AetherModuleFactory::create(
            Hellolocal::class,
            new AetherServiceLocator,
            ['foo' => 'bar']
        );

        $this->assertEquals('Hello local', $mod->run());
    }
}
