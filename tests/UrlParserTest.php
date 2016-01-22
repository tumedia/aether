<?php // vim:set ts=4 sw=4 et:

require_once(AETHER_PATH . 'lib/AetherUrlParser.php');

/**
 * 
 * Created: 2009-02-17
 * @author Raymond Julin
 * @package aether.test
 */

class AetherUrlParserTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherUrlParser'));
    }
    
    public function testParser() {
        $url = 'http://aether.raymond.raw.no/foobar/hello?foo';
        $parser = new AetherUrlParser;
        $parser->parse($url);
        $this->assertEquals($parser->get('scheme'), 'http');
        $user = $parser->get('user');
        $this->assertTrue(empty($user));

        $url2 = 'ftp://foo:bar@hw.no/world?bar';
        $parser = new AetherUrlParser;
        $parser->parse($url2);
        $this->assertEquals($parser->get('scheme'), 'ftp');
        $this->assertEquals($parser->get('user'), 'foo');
        $this->assertEquals($parser->get('pass'), 'bar');
        $this->assertEquals($parser->get('path'), '/world');

        $this->assertEquals($parser->__toString(), preg_replace('/\?.*/', '', $url2));
    }

    public function testParseServerArray() {
        $server = array(
            'HTTP_HOST' => 'aether.raymond.raw.no',
            'SERVER_NAME' => 'aether.raymond.raw.no',
            'SERVER_PORT' => 80,
            'AUTH_TYPE' => 'Basic',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foobar/hello?foo',
            'SCRIPT_NAME' => '/deployer.php',
            'PHP_SELF' => '/deployer.php',
            'REQUEST_TIME' => 1170332549
        );
        $parser = new AetherUrlParser;
        $parser->parseServerArray($server);
        $this->assertEquals($parser->get('scheme'), 'http');
        $this->assertEquals($parser->get('path'), '/foobar/hello');

        $server['PHP_AUTH_USER'] = 'foo';
        $server['PHP_AUTH_PW'] = 'bar';
        $parser->parseServerArray($server);
        $this->assertEquals($parser->get('user'), 'foo');
        $this->assertEquals($parser->get('pass'), 'bar');

        // Get as string again
        $this->assertEquals($parser->__toString(), 'http://foo:bar@aether.raymond.raw.no/foobar/hello');
    }
}
