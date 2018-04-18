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
    }
}
