<?php

namespace Tests;

use Aether\ServiceLocator;
use PHPUnit\Framework\TestCase;
use Aether\Modules\ModuleFactory;
use Tests\Fixtures\Modules\Hellolocal;

class ModuleFactoryTest extends TestCase
{
    public function testCreate()
    {
        $mod = ModuleFactory::create(
            Hellolocal::class,
            new ServiceLocator,
            ['foo' => 'bar']
        );

        $this->assertEquals('Hello local', $mod->run());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsWhenTheRequestedClassIsNotAModule()
    {
        ModuleFactory::create(self::class, new ServiceLocator);
    }
}
