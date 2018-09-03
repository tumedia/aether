<?php

namespace Aether\Assets;

use Aether\Providers\Provider;

class AssetsProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton(AssetResolver::class, function ($aether) {
            return new AssetResolver(
                $aether['files'],
                config('assets.uri_prefix', '/assets'),
                "{$aether['projectRoot']}public/assets"
            );
        });
    }
}
