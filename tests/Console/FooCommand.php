<?php

namespace Tests\Console;

use Aether\Console\Command;

class FooCommand extends Command
{
    protected $signature = 'test:foo-command {--text=}';

    protected $description = 'A dummy command';

    public function handle()
    {
        $this->info('Great success');

        if ($this->hasOption('text')) {
            $this->info($this->option('text'));
        }
    }
}
