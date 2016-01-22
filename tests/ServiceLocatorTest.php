<?php // vim:set ts=4 sw=4 et:

require_once(AETHER_PATH . 'lib/AetherServiceLocator.php');

/**
 * 
 * Created: 2009-02-17
 * @author Raymond Julin
 * @package aether.test
 */

class AetherServiceLocatorTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherServiceLocator'));
    }
    
    public function testCustomObjectStorage() {
        // Create a small class for testing
        $obj = new stdClass;
        $obj->foo = 'bar';
        $asl = new AetherServiceLocator;
        $asl->set('tester', $obj);
        $tester = $asl->get('tester');
        $this->assertSame($tester, $obj);
    }

    public function testArray() {
        $asl = new AetherServiceLocator;
        $arr = $asl->getVector('foo');
        $arr['foo'] = 'bar';
        $arr2 = $asl->getVector('foo');
        $this->assertEquals($arr['foo'], $arr2['foo']);
    }
}
