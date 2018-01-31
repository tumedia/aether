<?php

namespace Aether\Console;

use Aether\Aether;
use Aether\Console\Commands\GenerateConfigCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Application extends SymfonyApplication
{
    protected $aether;

    public function __construct(Aether $aether)
    {
        parent::__construct('Aether CLI');

        $this->aether = $aether;

        $this->add(new GenerateConfigCommand);
    }

    public function add(SymfonyCommand $command)
    {
        if ($command instanceof Command) {
            $command->setAether($this->aether);
        }

        return parent::add($command);
    }
}
