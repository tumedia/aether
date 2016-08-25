<?php // vim:set ts=4 sw=4 et:

class AetherJsonCommentFilteredResponseTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherJSONCommentFilteredResponse'));
    }

    public function testResponse() {
        $struct = array('foo'=>'bar',' bar'=>'foo');
        $res = new AetherJSONCommentFilteredResponse($struct);
        $out = $res->get();
        $this->assertTrue(strpos($out, '{"foo":"bar"," bar":"foo"}')!==false);
        $this->assertTrue(preg_match('/\/\*[^\*]+\*\//',$out)==true);
    }
}
