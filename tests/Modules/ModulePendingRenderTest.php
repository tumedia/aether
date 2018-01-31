<?php

namespace Tests;

use Aether\Modules\PendingRender;
use Tests\Fixtures\Modules\Hellolocal;
use Tests\Fixtures\Modules\OptionsSerializer;

class ModulePendingRenderTest extends TestCase
{
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
            ],
        ]);

        $this->assertOptions(['foo' => 'bar'], OptionsSerializer::draw('note'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testItThrowsWhenDynamicallyCallingANonExistingMethod()
    {
        $pending = new PendingRender(OptionsSerializer::class);

        $pending->fooBar();
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
