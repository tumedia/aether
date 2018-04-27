<?php

namespace Aether\Bootstrap;

use Aether\Aether;
use Aether\PackageDiscovery\Discoverer;

class RegisterProviders
{
    protected $aether;

    protected $frameworkProviders = [
        \Aether\Providers\AetherConfigProvider::class,
        \Aether\Providers\LocalizationProvider::class,
        \Aether\Cache\CacheProvider::class,
        \Aether\Session\SessionProvider::class,
        \Aether\Templating\TemplateProvider::class,
        \Aether\Providers\TimerProvider::class,
        \Aether\Providers\DatabaseProvider::class,
        \Aether\Console\AetherCliProvider::class,
    ];

    public function bootstrap(Aether $aether)
    {
        $this->aether = $aether;

        $aether->registerProviders($this->frameworkProviders);

        $config = $aether['config'];

        if (! $config->wasLoadedFromCompiled()) {
            $this->addPackageProvidersToConfig($config);
        }

        $aether->registerProviders($config->get('app.providers', []));
    }

    protected function addPackageProvidersToConfig($config)
    {
        if (! $config->has('app.providers')) {
            $config->set('app.providers', []);
        }

        $discoverer = $this->aether->make(Discoverer::class);

        foreach ($discoverer->getProvidersFromInstalledPackages() as $provider) {
            $config->push('app.providers', $provider);
        }
    }
}
