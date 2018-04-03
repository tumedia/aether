<?php

namespace Aether\Console;

use Aether\Aether;
use BadMethodCallException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Console\Kernel as KernelContract;

class Kernel implements KernelContract
{
    /**
     * @var \Aether\Aether
     */
    protected $aether;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * @var \Aether\Console\AetherCli
     */
    protected $aetherCli;

    public function __construct(Aether $aether, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'aether');
        }

        $this->aether = $aether;
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($input, $output = null)
    {
        // todo: error handling

        return $this->getAetherCli()->run($input, $output);
    }

    protected function getAetherCli()
    {
        if (is_null($this->aetherCli)) {
            $this->aetherCli = new AetherCli($this->aether, $this->events, 'dev-master');
        }

        return $this->aetherCli;
    }

    /**
     * {@inheritdoc}
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        return $this->getAetherCli()->call($command, $parameters, $outputBuffer);
    }

    /**
     * {@inheritdoc}
     */
    public function queue($command, array $parameters = [])
    {
        throw new BadMethodCallException('Kernel::queue() is not yet implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->getAetherCli()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->getAetherCli()->output();
    }
}
