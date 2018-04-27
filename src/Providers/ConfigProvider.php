<?php

namespace Aether\Providers;

use Aether\Config;

class ConfigProvider extends Provider
{
    public function register()
    {
        // Load the application config and bind it to the service locator, so
        // that we can easily access it anywhere in the code using the
        // `config()` helper function.
        $this->aether->singleton('config', function ($aether) {
            return new Config($aether['projectRoot']);
        });
    }
}
