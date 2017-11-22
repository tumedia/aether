<?php

namespace Tests;

use Aether\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testLoadingConfigFromProjectRoot()
    {
        $config = new Config(__DIR__.'/Fixtures');

        $this->assertSame([
            'bar' => [
                'foo' => 'bar',
            ],
            'foo' => [
                'lorem' => 'ipsum',
            ],
        ], $config->all());
    }
}
