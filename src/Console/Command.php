<?php

namespace Aether\Console;

use Aether\Aether;
use Illuminate\Console\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    protected $aether;

    public function setAether(Aether $aether)
    {
        $this->aether = $aether;

        $this->setLaravel($aether);
    }

    public function getAether()
    {
        return $this->aether;
    }

    public function getLaravel()
    {
        return $this->getAether();
    }
}
