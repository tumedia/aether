<?php

namespace Aether\Assets;

use Illuminate\Filesystem\Filesystem;

class AssetResolver
{
    protected $files;

    protected $assetsUriPrefix;

    protected $assetsPath;

    public function __construct(Filesystem $files, string $assetsUriPrefix, string $assetsPath)
    {
        $this->files = $files;
        $this->assetsUriPrefix = $assetsUriPrefix;
        $this->assetsPath = $assetsPath;
    }

    /**
     * Find the absolute URI to an asset.
     *
     * @param  string  $asset
     * @return string
     *
     * @throws \Aether\Assets\AssetNotFoundException  If the asset could not be found.
     */
    public function find(string $asset): string
    {
        $assetPath = "{$this->assetsPath}/{$asset}";

        if (! $this->files->exists($assetPath)) {
            throw AssetNotFoundException::forAsset($asset, $assetPath);
        }

        $uri = $this->uriPrefix($asset);

        return $this->cacheBust($uri, $assetPath);
    }

    protected function uriPrefix(string $asset): string
    {
        return "{$this->assetsUriPrefix}/{$asset}";
    }

    protected function cacheBust(string $uri, string $assetPath): string
    {
        $lastModified = $this->files->lastModified($assetPath);

        return "{$uri}?{$lastModified}";
    }
}
