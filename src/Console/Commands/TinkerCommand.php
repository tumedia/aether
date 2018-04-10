<?php

namespace Aether\Console\Commands;

use Psy\Shell;
use Psy\Configuration;
use Aether\Console\Command;
use Illuminate\Support\Collection;
use Aether\Console\Tinker\TinkerCasters;

class TinkerCommand extends Command
{
    protected $signature = 'tinker';

    protected $description = 'Interact with the application';

    public function handle()
    {
        $config = new Configuration([
            'updateCheck' => 'never'
        ]);

        $config->addCasters($this->getCasters());

        $shell = new Shell($config);

        $shell->run();
    }

    protected function getCasters()
    {
        return [
            Collection::class => TinkerCasters::class.'::castCollection',
        ];
    }
}
