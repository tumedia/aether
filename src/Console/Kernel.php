<?php

namespace Aether\Console;

use Error;
use Exception;
use Throwable;
use Aether\Aether;
use BadMethodCallException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Kernel
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

    /**
     * Application bootsrappers for the console kernel.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Aether\Bootstrap\HandleExceptions::class,
        \Aether\Bootstrap\SetRequestForConsole::class,
        \Aether\Bootstrap\RegisterProviders::class,
        \Aether\Bootstrap\BootProviders::class,
    ];

    public function __construct(Aether $aether, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'aether');
        }

        $this->aether = $aether;
        $this->events = $events;
    }

    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();

            return $this->getAetherCli()->run($input, $output);
        } catch (Throwable $e) {
            if ($e instanceof Error) {
                $e = new FatalThrowableError($e);
            }

            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    public function bootstrap()
    {
        $this->aether->bootstrapWith($this->bootstrappers);
    }

    protected function getAetherCli()
    {
        if (is_null($this->aetherCli)) {
            $this->aetherCli = new AetherCli($this->aether, $this->events, 'dev-master');
        }

        return $this->aetherCli;
    }

    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        return $this->getAetherCli()->call($command, $parameters, $outputBuffer);
    }

    public function queue($command, array $parameters = [])
    {
        throw new BadMethodCallException('Kernel::queue() is not yet implemented.');
    }

    public function all()
    {
        return $this->getAetherCli()->all();
    }

    public function output()
    {
        return $this->getAetherCli()->output();
    }

    protected function reportException(Exception $e)
    {
        $this->make(ExceptionHandler::class)->report($e);
    }

    protected function renderException($output, Exception $e)
    {
        $this->make(ExceptionHandler::class)->renderForConsole($output, $e);
    }
}
