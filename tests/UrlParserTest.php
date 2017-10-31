<?php

namespace Tests;

use AetherUrlParser;
use PHPUnit\Framework\TestCase;

class UrlParserTest extends TestCase
{
    public function testParser()
    {
        $parser = new AetherUrlParser;
        $parser->parse('http://aether.raymond.raw.no/foobar/hello?foo');

        $this->assertEquals('http', $parser->get('scheme'));

        $user = $parser->get('user');
        $this->assertEmpty($user);

        $url2 = 'ftp://foo:bar@hw.no/world?bar';
        $parser = new AetherUrlParser;
        $parser->parse($url2);

        $this->assertEquals('ftp', $parser->get('scheme'));
        $this->assertEquals('foo', $parser->get('user'));
        $this->assertEquals('bar', $parser->get('pass'));
        $this->assertEquals('/world', $parser->get('path'));

        $this->assertEquals($parser->__toString(), preg_replace('/\?.*/', '', $url2));
    }

    public function testParseServerArray()
    {
        $server = [
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
            'REQUEST_TIME' => 1170332549,
        ];

        $parser = new AetherUrlParser;
        $parser->parseServerArray($server);

        $this->assertEquals('http', $parser->get('scheme'));
        $this->assertEquals('/foobar/hello', $parser->get('path'));

        $server['PHP_AUTH_USER'] = 'foo';
        $server['PHP_AUTH_PW'] = 'bar';
        $parser->parseServerArray($server);

        $this->assertEquals('foo', $parser->get('user'));
        $this->assertEquals('bar', $parser->get('pass'));

        // Get as string again
        $this->assertEquals('http://foo:bar@aether.raymond.raw.no/foobar/hello', $parser->__toString());
    }
}
