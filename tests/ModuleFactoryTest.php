<?php // vim:set ts=4 sw=4 et:

class AetherModuleFactoryTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherModuleFactory'));
    }

    public function testCreate() {
        AetherModuleFactory::$strict = true;
        AetherModuleFactory::$path = __DIR__.'/fixtures';
        $mod = AetherModuleFactory::create('Hellolocal',
            new AetherServiceLocator,array('foo'=>'bar'));
        $this->assertEquals($mod->run(), 'Hello local');
    }

    public function testCreateModuleFromCustomFolder() {
        AetherModuleFactory::$strict = false;
        AetherModuleFactory::$path = __DIR__ . '/fixtures/modules';
        $mod = AetherModuleFactory::create('Hellolocal',
            new AetherServiceLocator,array('foo'=>'bar'));

        $this->assertEquals($mod->run(), 'Hello local');
    }

    public function testShouldNotAcceptMaliciousModuleNames() {
        AetherModuleFactory::$strict = false;
        AetherModuleFactory::$path = __DIR__ . '/fixtures/modules';

        $this->setExpectedException('AetherInvalidModuleNameException');

        $mod = AetherModuleFactory::create(
            '../sections/Testsection',
            new AetherServiceLocator,
            []
        );
    }
}
