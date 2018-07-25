<?php

namespace Tests\Modules;

use Exception;
use Mockery as m;
use Tests\TestCase;
use Aether\Modules\Module;
use Aether\Modules\PendingRender;
use Tests\Fixtures\Modules\Hellolocal;
use Tests\Fixtures\Modules\OptionsSerializer;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ModulePendingRenderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItRendersWhenCastToString()
    {
        $pending = new PendingRender(Hellolocal::class);

        $this->assertEquals('Hello local', (string)$pending);
    }

    public function testDynamicCallsToSetOptions()
    {
        $pending = new PendingRender(OptionsSerializer::class);

        $pending->withFoo('bar')->withBaz('qux');

        $this->assertOptions(['foo' => 'bar', 'baz' => 'qux'], $pending);
    }

    public function testMergeOptionsFromConfig()
    {
        $this->setModulesConfig([
            OptionsSerializer::class => [
                'note' => [
                    'kristoffer' => 'var',
                    'her' => 'hei',
                ],
            ],
        ]);

        $pending = new PendingRender(OptionsSerializer::class);

        $pending->merge('note');

        $this->assertOptions([
            'kristoffer' => 'var',
            'her' => 'hei',
        ], $pending);
    }

    public function testLoadingOptionsFromAetherConfigWhenLegacyModeIsEnabled()
    {
        $this->setUrl('http://raw.no/module-pending-render');

        $pending = new PendingRender(OptionsSerializer::class);

        $this->assertSame($pending, $pending->legacyMode());

        $this->assertOptions([
            'foo' => 'bar',
            'baz' => 'qux',
            'aether-says' => 'hi',
            'AetherRunningMode' => 'test',
        ], $pending);
    }

    public function testItDoesNotLoadFromAetherConfigWhenLegacyModeIsNotEnabled()
    {
        $this->setUrl('http://raw.no/module-pending-render');

        $pending = new PendingRender(OptionsSerializer::class);

        $this->assertOptions([], $pending);
    }

    public function testAllTogetherNow()
    {
        $this->setUrl('http://raw.no/module-pending-render');

        $this->setModulesConfig([
            OptionsSerializer::class => [
                'note' => [
                    'kristoffer' => 'var',
                    'her' => 'hei',
                ],
            ],
        ]);

        $pending = new PendingRender(OptionsSerializer::class);

        $pending->setOptions([
            'lorem' => 'ipsum',
        ]);

        $pending->merge('note')->withBallSize('xxl');

        $pending->legacyMode();

        $this->assertOptions([
            'foo' => 'bar',
            'baz' => 'qux',
            'aether-says' => 'hi',
            'AetherRunningMode' => 'test',
            'lorem' => 'ipsum',
            'kristoffer' => 'var',
            'her' => 'hei',
            'ballSize' => 'xxl',
        ], $pending);
    }

    public function testDrawMethodOnAetherModule()
    {
        $pending = OptionsSerializer::draw();

        $this->assertInstanceOf(PendingRender::class, $pending);

        $this->assertOptions([], $pending);
    }

    public function testDrawMethodSetsOptions()
    {
        $this->assertOptions(['foo' => 'bar'], OptionsSerializer::draw([
            'foo' => 'bar',
        ]));
    }

    public function testDrawMethodCallsMergeMethod()
    {
        $this->setModulesConfig([
            OptionsSerializer::class => [
                'note' => [
                    'foo' => 'bar',
                ],
                'another-one' => [
                    'another-one' => 'hell yes',
                ],
            ],
        ]);

        $this->assertOptions(['foo' => 'bar'], OptionsSerializer::draw('note'));

        $this->assertOptions([
            'foo' => 'bar',
            'another-one' => 'hell yes',
        ], OptionsSerializer::draw('note', 'another-one'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testItThrowsWhenDynamicallyCallingANonExistingMethod()
    {
        $pending = new PendingRender(OptionsSerializer::class);

        $pending->fooBar();
    }

    public function testItShouldReportExceptionsDirectlyToTheExceptionHandlerWhenCastingToStringInProduction()
    {
        config()->set('app.env', 'production');

        $handler = m::mock(ExceptionHandler::class);
        $handler->shouldReceive('report')->with(m::type(SuperCoolException::class));

        $this->aether->instance(ExceptionHandler::class, $handler);

        $pending = new PendingRender(FailingTestModule::class);

        $this->assertEquals('', (string) $pending);
    }

    public function testItShouldRenderExceptionsDirectlyUsingTheExceptionHandlerWhenCastingToStringLocally()
    {
        $response = m::mock(\Aether\Response\Response::class);
        $response->shouldReceive('draw');

        $handler = m::mock(ExceptionHandler::class);
        $handler->shouldReceive('render')->with(null, m::type(SuperCoolException::class))->andReturn($response);

        $this->aether->instance(ExceptionHandler::class, $handler);

        $pending = new PendingRender(FailingTestModule::class);

        $this->assertEquals('', (string) $pending);
    }

    public function testTheRunMethodRunsThroughTheServiceContainer()
    {
        $this->assertSame('foo', (string) ModuleWithInjectedService::draw());
    }

    private function assertOptions($expected, string $renderedModule)
    {
        $this->assertSame($expected, unserialize($renderedModule));
    }

    private function setModulesConfig($config)
    {
        config()->set('modules', $config);
    }
}

class SuperCoolException extends Exception
{
    //
}

class FailingTestModule extends Module
{
    public function run()
    {
        throw new SuperCoolException('this is an expected exception');
    }
}
