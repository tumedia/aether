<?php

namespace Aether\Console\Commands;

use Psy\Shell;
use Aether\Console\Command;

class TinkerCommand extends Command
{
    protected $signature = 'tinker';

    protected $description = 'Interact with the application';

    public function handle()
    {
        $shell = new Shell;

        $shell->run();
    }
}
