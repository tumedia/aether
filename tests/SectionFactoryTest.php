<?php // vim:set ts=4 sw=4 et:

class AetherSectionFactoryTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherSectionFactory'));
    }

    public function testCreate() {
        AetherSectionFactory::$strict = true;
        AetherSectionFactory::$path = __DIR__ . '/fixtures';
        $section = AetherSectionFactory::create('Testsection', new AetherServiceLocator);
        $this->assertTrue(is_subclass_of($section, 'AetherSection'));
        $this->assertEquals(get_class($section), 'Testsection');
    }
}
