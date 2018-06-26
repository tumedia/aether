<?php

namespace Tests\Modules;

use Tests\TestCase;
use Aether\Modules\ModuleFactory;
use Tests\Fixtures\Modules\Hellolocal;

class ModuleFactoryTest extends TestCase
{
    public function testCreate()
    {
        $module = $this->getModuleFactory()->create(
            Hellolocal::class,
            ['foo' => 'bar']
        );

        $this->assertEquals('Hello local', $module->run());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsWhenTheRequestedClassIsNotAModule()
    {
        resolve(ModuleFactory::class)->create(self::class);
    }

    public function testTheRunMethodRunsThroughTheServiceContainer()
    {
        $factory = $this->getModuleFactory();
        $module = $factory->create(ModuleWithInjectedService::class);

        $this->assertSame('foo', $factory->run($module));
    }

    protected function getModuleFactory()
    {
        return $this->aether->make(ModuleFactory::class);
    }
}
