<?php

namespace Aether\PackageDiscovery;

use Aether\Services\Service;

class PackageDiscoveryService extends Service
{
    public function register()
    {
        $config = $this->container['config'];

        if ($config->wasLoadedFromCompiled()) {
            return;
        }

        if (! $config->has('app.services')) {
            $config->set('app.services', []);
        }

        foreach ($this->getDiscoverer()->getServicesFromInstalledPackages() as $service) {
            $config->push('app.services', $service);
        }
    }

    protected function getDiscoverer()
    {
        return new Discoverer($this->container['projectRoot']);
    }
}
