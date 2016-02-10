<?php // vim:set ts=4 sw=4 et:

require_once(AETHER_PATH . 'lib/AetherModuleFactory.php');
require_once(AETHER_PATH . 'lib/AetherServiceLocator.php');

/**
 * 
 * Created: 2009-02-17
 * @author Raymond Julin
 * @package aether.test
 */

class AetherModuleFactoryTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherModuleFactory'));
    }

    public function testCreate() {
        AetherModuleFactory::$strict = true;
        AetherModuleFactory::$path = AETHER_PATH;
        $mod = AetherModuleFactory::create('Helloworld', 
            new AetherServiceLocator,array('foo'=>'bar'));
        $this->assertEquals($mod->run(), 'Hello world');
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

        $this->setExpectedException(AetherInvalidModuleNameException::class);

        $mod = AetherModuleFactory::create(
            '../sections/Testsection',
            new AetherServiceLocator,
            []
        );
    }
}
