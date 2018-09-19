<?php

namespace Aether\Assets;

use Exception;

class AssetNotFoundException extends Exception
{
    public static function forAsset($asset, $assetPath)
    {
        return new static("Asset [{$asset}] not found at [{$assetPath}]");
    }
}
