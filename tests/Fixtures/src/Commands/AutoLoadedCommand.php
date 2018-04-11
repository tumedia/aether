<?php

namespace Tests\Fixtures\Commands;

use Aether\Console\Command;

class AutoLoadedCommand extends Command
{
    protected $signature = 'autoloaded';

    public function handle()
    {
        $this->info('Hello world');
    }
}
