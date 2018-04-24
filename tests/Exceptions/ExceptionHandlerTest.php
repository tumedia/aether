<?php

namespace Tests\Exceptions;

use Exception;
use Mockery as m;
use Aether\Aether;
use Aether\Exceptions\Handler;
use PHPUnit\Framework\TestCase;
use Aether\Bootstrap\HandleExceptions;
use Aether\PackageDiscovery\Discoverer;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\BufferedOutput;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ExceptionHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected static $packageVersionsFixture = [
        ['name' => 'foo/bar', 'version' => '0.1', 'reference' => 'ffffffff'],
    ];

    public function tearDown()
    {
        Aether::setInstance(null);
    }

    public function testReportingToSentry()
    {
        $aether = m::mock(Aether::class)->makePartial();
        $aether->shouldReceive('isProduction')->andReturn(true);

        $aether->instance(Discoverer::class, $this->mockPackageDiscoverer());

        $e = new Exception('expected exception');

        $aether->instance('sentry.client', $this->mockSentryClient($e));

        (new Handler($aether))->report($e);
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionsAreRenderedUsingWhoopsInDevelopment()
    {
        $aether = m::mock(Aether::class);
        $aether->shouldReceive('isProduction')->andReturn(false);

        $handler = new Handler($aether);

        $response = $handler->render(null, new Exception('expected'))->get();

        $this->assertContains('Whoops\Handler\PrettyPageHandler', $response);
    }

    public function testExceptionsAreRenderedInProduction()
    {
        $aether = m::mock(Aether::class)->makePartial();
        $aether->shouldReceive('isProduction')->andReturn(true);

        $handler = new Handler($aether);

        $response = $handler->render(null, new Exception('expected'))->get();

        $this->assertContains('Noe gikk galt', $response);
    }

    public function testExceptionsAreRenderForTheConsole()
    {
        $message = 'this is an expected exception';

        $output = new BufferedOutput;

        (new Handler(new Aether))->renderForConsole($output, new Exception($message));

        $this->assertContains($message, $output->fetch());
    }

    protected function mockPackageDiscoverer()
    {
        $m = m::mock(Discoverer::class);
        $m->shouldReceive('getPackageVersions')->andReturn(self::$packageVersionsFixture);
        return $m;
    }

    protected function mockSentryClient($expectedException)
    {
        $raven = m::mock('Raven_Client');
        $raven->shouldReceive('captureException')
              ->with($expectedException, ['modules' => ['foo/bar' => '0.1 (ffffffff)']])
              ->andReturn('exception_id');
        return $raven;
    }
}
