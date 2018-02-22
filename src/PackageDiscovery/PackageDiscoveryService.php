<?php

namespace Aether\PackageDiscovery;

use Aether\Services\Service;

class PackageDiscoveryService extends Service
{
    public function register()
    {
        $config = $this->container['config'];

        $this->container->bind(Discoverer::class, function ($app) {
            return new Discoverer($app['projectRoot']);
        });

        if (! $config->wasLoadedFromCompiled()) {
            $this->addPackageServicesToConfig($config);
        }
    }

    protected function addPackageServicesToConfig($config)
    {
        if (! $config->has('app.services')) {
            $config->set('app.services', []);
        }

        $discoverer = $this->container[Discoverer::class];

        foreach ($discoverer->getServicesFromInstalledPackages() as $service) {
            $config->push('app.services', $service);
        }
    }
}
