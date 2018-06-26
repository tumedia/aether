<?php

namespace Tests\Session;

use Tests\Traits\FileCache;
use PHPUnit\Framework\TestCase;
use Aether\Session\CacheSessionHandler;

class CacheHandlerTest extends TestCase
{
    use FileCache;

    protected $handler;

    protected function setUp()
    {
        $this->handler = new CacheSessionHandler(
            $this->setUpFileCache()
        );
    }

    protected function tearDown()
    {
        $this->tearDownFileCache();
    }

    public function testReadAndWrite()
    {
        $this->assertEmpty($this->handler->read('foo'));

        $this->assertTrue($this->handler->write('foo', 'bar'));

        $this->assertSame('bar', $this->handler->read('foo'));
    }

    public function testDestroy()
    {
        $this->handler->write('foo', 'bar');

        $this->assertSame('bar', $this->handler->read('foo'));

        $this->assertTrue($this->handler->destroy('foo'));

        $this->assertEmpty($this->handler->read('foo'));
    }

    public function testOpenMethodExistsButDoesntDoAnything()
    {
        $this->assertTrue($this->handler->open('foo', 'bar'));
    }

    public function testCloseMethodExistsButDoesntDoAnything()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testGcMethodExistsButDoesntDoAnything()
    {
        $this->assertTrue($this->handler->gc(0));
    }
}
