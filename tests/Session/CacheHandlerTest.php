<?php

namespace Tests;

use Tests\Traits\FileCache;
use AetherSessionHandlerCache;
use PHPUnit\Framework\TestCase;

class CacheHandlerTest extends TestCase
{
    use FileCache;

    protected $handler;

    protected function setUp()
    {
        $this->handler = new AetherSessionHandlerCache(
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
}
