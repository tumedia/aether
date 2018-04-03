<?php

namespace Aether\Console;

use Aether\Providers\Provider;

class AetherCliProvider extends Provider
{
    public function register()
    {
        $this->commands([
            Commands\ConfigClearCommand::class,
            Commands\ConfigGenerateCommand::class,
            Commands\TinkerCommand::class,
        ]);
    }
}
