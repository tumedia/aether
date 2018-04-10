<?php

namespace Tests;

use Aether\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $expectedConfig = [
        'bar' => [
            'foo' => 'bar',
            'bar' => 'env override',
        ],
        'foo' => [
            'lorem' => 'ipsum',
        ]
    ];

    public function testLoadingConfigFromProjectRoot()
    {
        $config = $this->getConfig();

        $this->assertSame($this->expectedConfig, $config->all());
    }

    public function testCompilingTheConfig()
    {
        $config = $this->getConfig();

        $this->assertFalse($config->wasLoadedFromCompiled());

        $config->saveToFile($file = __DIR__.'/Fixtures/config/compiled.php');

        $config = $this->getConfig();

        $this->assertTrue($config->wasLoadedFromCompiled());

        $this->assertSame($this->expectedConfig, $config->all());

        unlink($file);
    }

    protected function getConfig()
    {
        return new Config(__DIR__.'/Fixtures');
    }
}
