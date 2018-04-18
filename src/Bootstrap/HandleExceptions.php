<?php

namespace Aether\Bootstrap;

use Exception;
use Aether\Aether;
use ErrorException;
use Symfony\Component\Debug\DebugClassLoader;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class HandleExceptions
{
    public function bootstrap(Aether $aether)
    {
        $this->aether = $aether;

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutDown']);

        if ($aether->isProduction()) {
            ini_set('display_errors', 'Off');
        }

        // @todo:
        DebugClassLoader::enable();
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @throws \ErrorException
     */
    public function handleError($severity, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    }

    public function handleException($e)
    {
        if (! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        try {
            $this->getHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        if ($this->aether->runningInConsole()) {
            $this->getHandler()->renderForConsole(new ConsoleOutput, $e);
        } else {
            $this->getHandler()->render(null, $e)->draw($this->aether);
        }
    }

    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    protected function getHandler()
    {
        return $this->aether->make(ExceptionHandler::class);
    }
}
