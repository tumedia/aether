<?php

namespace Tests;

use AetherCacheFiles;
use Tests\Traits\FileCache;
use PHPUnit\Framework\TestCase;

class FilesCacheTest extends TestCase
{
    use FileCache;

    protected $cache;

    protected function setUp()
    {
        $this->cache = $this->setUpFileCache();
    }

    protected function tearDown()
    {
        $this->tearDownFileCache();
    }

    public function testReadAndWrite()
    {
        $this->assertFalse($this->cache->get('foo'));

        $this->assertTrue($this->cache->set('foo', 'bar'));

        $this->assertSame('bar', $this->cache->get('foo'));
    }

    public function testSettingTtl()
    {
        $this->cache->set('foo', 'bar', 10);
        $this->assertSame('bar', $this->cache->get('foo'));

        $this->cache->set('foo', 'bar', -10);
        $this->assertFalse($this->cache->get('foo'));
    }

    public function testGettingWithMaxAgeSpecified()
    {
        $this->cache->set('foo', 'bar');

        $this->assertSame('bar', $this->cache->get('foo', 10));

        $this->assertFalse($this->cache->get('foo', -10));
    }

    public function testRemove()
    {
        $this->cache->set('foo', 'bar');

        $this->assertTrue($this->cache->rm('foo'));

        $this->assertFalse($this->cache->get('foo'));
    }

    public function testHas()
    {
        $this->assertFalse($this->cache->has('foo'));

        $this->cache->set('foo', 'bar');

        $this->assertTrue($this->cache->has('foo'));
    }
}
