<?php

namespace Aether\PackageDiscovery;

use Illuminate\Support\Arr;

class Discoverer
{
    protected $projectRoot;

    public function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function getServicesFromInstalledPackages()
    {
        return Arr::flatten($this->mapInstalledPackages(function ($package) {
            return $package->extra->aether->services ?? [];
        }));
    }

    public function getPackageVersions()
    {
        // todo: add this info to sentry
        return $this->mapInstalledPackages(function ($package) {
            return [
                'name' => $package->name,
                'version' => $package->version,
                'reference' => $this->getPackageReference($package),
            ];
        });
    }

    protected function mapInstalledPackages($callback)
    {
        return array_map($callback, $this->getInstalledPackages());
    }

    protected function getInstalledPackages()
    {
        $path = $this->vendorPath('composer/installed.json');

        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path));
    }

    protected function vendorPath($path = null)
    {
        return $this->projectRoot.'vendor'.(is_null($path) ? '' : '/'.$path);
    }

    protected function getPackageReference($package)
    {
        $ref = object_get($package, 'dist.reference')
            ?: object_get($package, 'source.reference');

        return substr($ref, 0, 12);
    }
}
