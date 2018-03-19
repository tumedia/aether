<?php

namespace Tests\PackageDiscovery;

use PHPUnit\Framework\TestCase;
use Aether\PackageDiscovery\Discoverer;

class DiscovererTest extends TestCase
{
    public function testGettingPackageVersions()
    {
        $discoverer = $this->getDiscoverer();

        $this->assertEquals([
            ['name' => 'neo/import', 'version' => 'dev-master', 'reference' => '346d19b7def6'],
            ['name' => 'smarty/smarty', 'version' => 'v3.1.30', 'reference' => 'ed2b7f1146cf'],
            ['name' => 'tumedia/aether', 'version' => 'dev-master', 'reference' => '94b680ec5158'],
        ], $discoverer->getPackageVersions());
    }

    public function testItReturnsEmptyArrayIfComposerIsNotInstalled()
    {
        $discoverer = $this->getDiscoverer('i-do-not-exist/');

        $this->assertEquals([], $discoverer->getPackageVersions());
        $this->assertEquals([], $discoverer->getServicesFromInstalledPackages());
    }

    public function testGettingAetherServicesFromPackages()
    {
        $discoverer = $this->getDiscoverer();

        $this->assertEquals([
            'Neo\Import\FooService',
            'Neo\Import\BarService',
            'Not\So\Smarty',
        ], $discoverer->getServicesFromInstalledPackages());
    }

    protected function getDiscoverer($path = 'fixtures/')
    {
        return new Discoverer(__DIR__.'/'.$path);
    }
}
