<?php

namespace Aether\Exceptions;

use Exception;
use Aether\Aether;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;
use Aether\PackageDiscovery\Discoverer;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

class Handler implements ExceptionHandler
{
    protected $aether;

    protected $context = [];

    /**
     * If supported by the reporting mechanism, this should contain a unique ID
     * that can be linked to the thrown exception.
     *
     * @var string
     */
    protected $lastReportedId;

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @param  array  $context = []
     * @return void
     */
    public function report(Exception $e, array $context = [])
    {
        if ($this->aether->isProduction()) {
            $this->reportInProduction($e, $context);
        } else {
            $this->reportInDevelopment($e, $context);
        }

        // todo: figure out if we should also send to regular logs in prod
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request  (Note: Not yet in use)
     * @param  \Exception  $e
     * @return \Aether\Response\Response
     */
    public function render($request, Exception $e)
    {
        $response = new ExceptionResponse($e, $this->getLastReportedId());

        if ($this->aether->isProduction()) {
            $response->setContent($this->responseForProduction($e));
        } else {
            $response->setContent($this->responseForDevelopment($e));
        }

        return $response;
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Exception  $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        if (! $this->aether->isProduction()) {
            $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        (new ConsoleApplication)->renderException($e, $output);
    }

    /**
     * Add global context that will be added to all reported exceptions.
     *
     * @param  array  $context
     * @return void
     */
    public function addContext(array $context)
    {
        $this->context = array_merge($this->context, $context);
    }

    /**
     * Return a unique ID corresponding to the previously caught exception.
     *
     * @return string
     */
    protected function getLastReportedId()
    {
        return $this->lastReportedId;
    }

    protected function responseForProduction(Exception $e)
    {
        $id = $this->getLastReportedId();

        return "<h1>Noe gikk galt</h1>".($id ? "<p>ID: <code>{$id}</code></p>" : "");
    }

    protected function responseForDevelopment(Exception $e)
    {
        return tap(new Whoops, function ($whoops) {
            $whoops->pushHandler($this->getWhoopsHandler());

            $whoops->writeToOutput(false);

            $whoops->allowQuit(false);
        })->handleException($e);
    }

    protected function getWhoopsHandler()
    {
        return tap(new PrettyPageHandler, function ($handler) {
            $handler->handleUnconditionally(true);
        });
    }

    protected function reportInDevelopment(Exception $e, array $context = [])
    {
        // todo: write to some log file?
    }

    protected function reportInProduction(Exception $e, array $context = [])
    {
        $eventId = $this->aether['sentry.client']->captureException(
            $e,
            $context + $this->getContext()
        );

        $this->lastReportedId = $eventId;
    }

    protected function getContext()
    {
        return ['modules' => $this->getInstalledPackages()] + $this->context;
    }

    protected function getInstalledPackages()
    {
        $packages = [];

        foreach ($this->aether->make(Discoverer::class)->getPackageVersions() as $package) {
            $packages[$package['name']] = "{$package['version']} ({$package['reference']})";
        }

        return $packages;
    }
}
