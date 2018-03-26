<?php

namespace Aether\Console;

use Aether\Aether;
use Aether\Console\Commands\TinkerCommand;
use Aether\Console\Commands\ConfigClearCommand;
use Aether\Console\Commands\ConfigGenerateCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Application extends SymfonyApplication
{
    protected $aether;

    public function __construct(Aether $aether)
    {
        parent::__construct('Aether CLI');

        $this->aether = $aether;

        // $this->setAutoExit(false);
        // $this->setCatchExceptions(false);

        // todo: move this
        $this->add(new ConfigClearCommand);
        $this->add(new ConfigGenerateCommand);
        $this->add(new TinkerCommand);
    }

    public function add(SymfonyCommand $command)
    {
        if ($command instanceof Command) {
            $command->setAether($this->aether);
        }

        return parent::add($command);
    }
}
