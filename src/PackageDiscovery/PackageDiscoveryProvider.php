<?php

namespace Aether\PackageDiscovery;

use Aether\Providers\Provider;

class PackageDiscoveryProvider extends Provider
{
    public function register()
    {
        $this->aether->bind(Discoverer::class, function ($app) {
            return new Discoverer($app['projectRoot']);
        });

        $config = $this->aether['config'];

        if (! $config->wasLoadedFromCompiled()) {
            $this->addPackageProvidersToConfig($config);
        }

        $this->aether->registerProviders($config['app.providers']);
    }

    protected function addPackageProvidersToConfig($config)
    {
        if (! $config->has('app.providers')) {
            $config->set('app.providers', []);
        }

        $discoverer = $this->aether[Discoverer::class];

        foreach ($discoverer->getProvidersFromInstalledPackages() as $provider) {
            $config->push('app.providers', $provider);
        }
    }
}
