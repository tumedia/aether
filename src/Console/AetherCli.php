<?php

namespace Aether\Console;

use Aether\Aether;
use Illuminate\Console\Application;
use Illuminate\Contracts\Events\Dispatcher;

class AetherCli extends Application
{
    /**
     * @var \Aether\Aether
     */
    protected $aether;

    public function __construct(Aether $aether, Dispatcher $events, $version)
    {
        parent::__construct($aether, $events, $version);

        $this->aether = $aether;

        $this->setCatchExceptions(false);
        $this->setAutoExit(false);
    }
}
