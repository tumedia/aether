<?php // vim:set ts=4 sw=4 et:

class AetherJsonResponseTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherJSONResponse'));
    }

    public function testResponse() {
        $struct = array('foo'=>'bar',' bar'=>'foo');
        $res = new AetherJSONResponse($struct);
        $out = $res->get();

        $this->assertEquals(json_encode($out), '{"foo":"bar"," bar":"foo"}');
    }
}
