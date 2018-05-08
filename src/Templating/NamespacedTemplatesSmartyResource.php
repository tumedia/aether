<?php

namespace Aether\Templating;

use Illuminate\Support\Str;
use Smarty_Resource_Custom;

class NamespacedTemplatesSmartyResource extends Smarty_Resource_Custom
{
    protected $path;

    protected $namespace;

    protected $projectRoot;

    public function __construct($path, $namespace, $projectRoot)
    {
        $this->path = $path;
        $this->namespace = $namespace;
        $this->projectRoot = rtrim($projectRoot, '/');
    }

    protected function fetch($name, &$source, &$mtime)
    {
        $source = file_get_contents($path = $this->pathToTemplate($name));

        if ($source) {
            $mtime = $this->fetchTimestamp($name);
        }
    }

    protected function fetchTimestamp($name)
    {
        return filemtime($this->pathToTemplate($name));
    }

    protected function pathToTemplate($name)
    {
        if ($forceOriginal = Str::startsWith($name, '!')) {
            $name = substr($name, 1);
        }

        if (! $forceOriginal &&
            file_exists($localOverride = $this->localOverridePath($name))) {
            return $localOverride;
        }

        return "{$this->path}/{$name}";
    }

    protected function localOverridePath($name)
    {
        return "{$this->projectRoot}/templates/vendor/{$this->namespace}/{$name}";
    }
}
