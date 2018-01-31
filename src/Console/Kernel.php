<?php

namespace Aether\Console;

use Aether\Aether;
use BadMethodCallException;
use Illuminate\Contracts\Console\Kernel as KernelContract;

class Kernel implements KernelContract
{
    protected $aether;

    protected $aetherCli;

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($input, $output = null)
    {
        return $this->getAetherCli()->run($input, $output);
    }

    protected function getAetherCli()
    {
        if (is_null($this->aetherCli)) {
            $this->aetherCli = new Application($this->aether);
        }

        return $this->aetherCli;
    }

    /**
     * {@inheritdoc}
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        return $this->getAetherCli()->call($command, $parameters, $outpuBuffer);
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
