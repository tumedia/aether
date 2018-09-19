<?php

namespace Aether\Assets;

use Exception;

class AssetNotFoundException extends Exception
{
    public static function forAsset($asset, $assetPath)
    {
        $message = "Asset [{$asset}] not found at [{$assetPath}]";

        if (! app()->isProduction()) {
            $message .= "\n\nHint: Try running \"npm run watch\"";
        }

        return new static($message);
    }
}
