<?php

namespace Tests;

use Aether;
use AetherConfig;
use AetherUrlParser;
use ReflectionClass;
use AetherServiceLocator;
use AetherModulePendingRender;
use PHPUnit\Framework\TestCase;
use Illuminate\Config\Repository;
use Tests\Fixtures\Modules\Hellolocal;
use Tests\Fixtures\Modules\OptionsSerializer;

class ModulePendingRenderTest extends TestCase
{
    protected $sl;

    protected function setUp()
    {
        Aether::setInstance(
            // Because the Aether constructor is  i n s a n e .
            $aether = (new ReflectionClass(Aether::class))->newInstanceWithoutConstructor()
        );

        $this->sl = new AetherServiceLocator;

        $aether->setServiceLocator($this->sl);
    }

    protected function tearDown()
    {
        $this->sl = null;

        Aether::setInstance(null);
    }

    public function testItRendersWhenCastToString()
    {
        $pending = new AetherModulePendingRender(Hellolocal::class);

        $this->assertEquals('Hello local', (string)$pending);
    }

    public function testDynamicCallsToSetOptions()
    {
        $pending = new AetherModulePendingRender(OptionsSerializer::class);

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

        $pending = new AetherModulePendingRender(OptionsSerializer::class);

        $pending->merge('note');

        $this->assertOptions([
            'kristoffer' => 'var',
            'her' => 'hei',
        ], $pending);
    }

    public function testLoadingOptionsFromAetherConfig()
    {
        $this->setAetherConfigUsingUrl('http://raw.no/module-pending-render');

        $pending = new AetherModulePendingRender(OptionsSerializer::class);

        $this->assertOptions([
            'foo' => 'bar',
            'baz' => 'qux',
            'aether-says' => 'hi',
        ], $pending);
    }

    public function testAllTogetherNow()
    {
        $this->setAetherConfigUsingUrl('http://raw.no/module-pending-render');

        $this->setModulesConfig([
            OptionsSerializer::class => [
                'note' => [
                    'kristoffer' => 'var',
                    'her' => 'hei',
                ],
            ],
        ]);

        $pending = new AetherModulePendingRender(OptionsSerializer::class);

        $pending->setOptions([
            'lorem' => 'ipsum',
        ]);

        $pending->merge('note')->withBallSize('xxl');

        $this->assertOptions([
            'foo' => 'bar',
            'baz' => 'qux',
            'aether-says' => 'hi',
            'lorem' => 'ipsum',
            'kristoffer' => 'var',
            'her' => 'hei',
            'ballSize' => 'xxl',
        ], $pending);
    }

    public function testDrawMethodOnAetherModule()
    {
        $pending = OptionsSerializer::draw();

        $this->assertInstanceOf(AetherModulePendingRender::class, $pending);

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

    private function assertOptions($expected, string $renderedModule)
    {
        $this->assertSame($expected, unserialize($renderedModule));
    }

    private function setModulesConfig($config)
    {
        $this->sl->set('config', new Repository([
            'modules' => $config,
        ]));
    }

    private function setAetherConfigUsingUrl($url)
    {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $config = new AetherConfig(__DIR__.'/Fixtures/aether.config.xml');
        $config->matchUrl($aetherUrl);

        $this->sl->set('aetherConfig', $config);

        return $config;
    }
}
