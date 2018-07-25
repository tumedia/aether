<?php

namespace Aether\Providers;

use Aether\Localization;

class LocalizationProvider extends Provider
{
    public function boot()
    {
        $this->aether->instance('localization', new Localization(
            $this->aether['aetherConfig']->getOptions(),
            $this->aether['projectRoot'] . 'locale'
        ));
    }
}
