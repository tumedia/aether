<?php // vim:set ts=4 sw=4 et:

class AetherModuleFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testEnvironment()
    {
        $this->assertTrue(class_exists('AetherModuleFactory'));
    }

    public function testCreate()
    {
        $mod = AetherModuleFactory::create(
            'Hellolocal',
            new AetherServiceLocator,
            ['foo' => 'bar']
        );

        $this->assertEquals($mod->run(), 'Hello local');
    }
}
