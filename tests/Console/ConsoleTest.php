<?php

namespace Tests\Console;

use Tests\TestCase;
use Aether\Console\Kernel;
use Aether\Console\AetherCli;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleTest extends TestCase
{
    protected $console;

    protected function setUp()
    {
        parent::setUp();

        $this->console = $this->aether->make(Kernel::class);

        AetherCli::starting(function ($aetherCli) {
            $aetherCli->resolve(FooCommand::class);
        });
    }

    public function testHandleMethod()
    {
        $input = new ArgvInput(['aether', 'test:foo-command']);
        $output = new BufferedOutput;

        $this->assertEquals(0, $this->console->handle($input, $output));

        $this->assertContains('Great success', $output->fetch());
    }

    public function testAllMethod()
    {
        $allCommands = array_keys($this->console->all());

        $this->assertContains('test:foo-command', $allCommands);
    }

    public function testCallingACommand()
    {
        $status = $this->console->call('test:foo-command');

        $this->assertEquals(0, $status);

        $this->assertContains('Great success', $this->console->output());
    }

    public function testCallingACommandWithAnOption()
    {
        $this->console->call('test:foo-command', ['--text' => 'Yes hello']);

        $this->assertContains('Yes hello', $this->console->output());
    }
}
