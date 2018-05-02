<?php

namespace Aether\Providers;

use Illuminate\Filesystem\Filesystem;

class FilesystemProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('files', function () {
            return new Filesystem;
        });
    }
}
