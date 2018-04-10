<?php

namespace Tests\PackageDiscovery;

use Tests\TestCase;
use Tests\Fixtures\TestPackage\TestPackageProvider;

class ProviderRegisteringTest extends TestCase
{
    public function testTheDiscoveredProviderIsAddedToConfig()
    {
        $providers = $this->aether['config']['app.providers'];

        $this->assertContains(TestPackageProvider::class, $providers);
    }

    public function testTheProviderIsRegistered()
    {
        $this->assertEquals('bar', $this->aether['test.package.foo']);
    }

    public function testTheProviderIsBooted()
    {
        $this->assertTrue($this->aether['test.package.booted']);
    }

    public function testTheProviderFilledInConfigValues()
    {
        $config = $this->aether['config']['test-package'];

        $expected = ['foo' => 'bar'];

        $this->assertEquals($expected, $config);
    }
}
