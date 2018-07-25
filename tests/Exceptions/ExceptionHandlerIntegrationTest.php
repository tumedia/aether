<?php

namespace Tests\Exceptions;

use Exception;
use Mockery as m;
use Raven_Client;
use Tests\TestCase;
use Aether\Console\Command;
use Aether\Console\AetherCli;
use Aether\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\BufferedOutput;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ExceptionHandlerIntegrationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @expectedException \ErrorException
     * @expectedExceptionMessage Use of undefined constant foo
     */
    public function testPhpErrorsAreConvertedToErrorExceptions()
    {
        foo;
    }

    public function testPhpErrorsAreOnlyReportedInProduction()
    {
        config()->set('app.env', 'production');

        $handler = m::mock(ExceptionHandler::class);
        $handler->shouldNotReceive('render');
        $handler->shouldReceive('report')->with(m::type('ErrorException'), [
            'tags' => ['new_error_exception' => 'yup'],
        ]);

        foo;
    }

    /**
     * @runInSeparateProcess
     */
    public function testThrownExceptionsAreProcessedByTheHandler()
    {
        config()->set('app.env', 'production');

        $this->aether->instance('sentry.client', $this->mockSentry());

        $this->visit('http://raw.no/exception-rendering-production')
             ->assertStatus(500)
             ->assertSee('Noe gikk galt')
             ->assertSee('unique-error-id');
    }

    public function testExceptionsThrownInConsoleCommandsAreHandledByTheKernel()
    {
        config()->set('app.env', 'production');

        $this->aether->instance('sentry.client', $this->mockSentry());

        AetherCli::starting(function ($aetherCli) {
            $aetherCli->resolve(FailingConsoleCommand::class);
        });

        $output = new BufferedOutput;
        $kernel = $this->aether->make(ConsoleKernel::class);

        $this->assertEquals(1, $kernel->handle(new ArgvInput(['aether', 'test:fail-hard']), $output));

        $this->assertContains('This is an expected exeption', $output->fetch());
    }

    protected function mockSentry()
    {
        $sentry = m::mock(Raven_Client::class);
        $sentry->shouldReceive('captureException')->andReturn('unique-error-id');
        return $sentry;
    }
}

class FailingConsoleCommand extends Command
{
    protected $signature = 'test:fail-hard';

    public function handle()
    {
        throw new Exception('This is an expected exeption');
    }
}
