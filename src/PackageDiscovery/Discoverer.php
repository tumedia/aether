<?php

namespace Aether\PackageDiscovery;

use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;

class Discoverer
{
    protected $projectRoot;

    protected $files;

    public function __construct($projectRoot, Filesystem $files)
    {
        $this->projectRoot = $projectRoot;
        $this->files = $files;
    }

    public function getProvidersFromInstalledPackages()
    {
        return Arr::collapse($this->mapInstalledPackages(function ($package) {
            return $package->extra->aether->providers ?? [];
        }));
    }

    public function getPackageVersions()
    {
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

        if (! $this->files->exists($path)) {
            return [];
        }

        return json_decode($this->files->get($path));
    }

    protected function vendorPath($path = null)
    {
        return $this->projectRoot.'vendor'.(is_null($path) ? '' : '/'.$path);
    }

    protected function getPackageReference($package)
    {
        $ref = $package->{$package->{'installation-source'}}->reference;

        return substr($ref, 0, 12);
    }
}
