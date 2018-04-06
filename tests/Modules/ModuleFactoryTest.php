<?php

namespace Tests;

use Tests\TestCase;
use Aether\ServiceLocator;
use Aether\Modules\ModuleFactory;
use Tests\Fixtures\Modules\Hellolocal;

class ModuleFactoryTest extends TestCase
{
    public function testCreate()
    {
        $mod = ModuleFactory::create(
            Hellolocal::class,
            $this->aether,
            ['foo' => 'bar']
        );

        $this->assertEquals('Hello local', $mod->run());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsWhenTheRequestedClassIsNotAModule()
    {
        ModuleFactory::create(self::class, $this->aether);
    }
}
