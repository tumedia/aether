<?php

namespace Tests\AetherConfig;

use Mockery as m;
use Aether\Aether;
use Aether\UrlParser;
use Aether\AetherConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;
use Tests\Fixtures\Sections\NotFoundSection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class AetherConfigTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $aetherConfig;

    protected $aetherConfigFiles;

    protected $aetherConfigOptions;

    public function tearDown()
    {
        Aether::setInstance(null);
    }

    public function testConfigReadDefault()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <option name="foo">bar</option>
                <section>FooSection</section>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'bar')
            ->assertSection('FooSection');
    }

    public function testConfigReadDefaultBase()
    {
        $this->givenUrlRules('
            <rule default="true">
                <option name="foo">bar</option>
                <section>FooSection</section>
            </rule>')
            ->andUrl('http://test/404')
            ->assertOption('foo', 'bar')
            ->assertSection('FooSection');
    }

    public function testConfigAssembleOptionsCorrectly()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <module>
                    FooModule
                    <option name="foo">bar</option>
                </module>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertModule('FooModule')
            ->assertModuleOption('FooModule', 'foo', 'bar');
    }

    public function testConfigFindParentDefault()
    {
        $this->givenUrlRules('
            <option name="foo">bar</option>
            <rule match="foo">
                <option name="baz">qux</option>
            </rule>
            <rule match="bar">
                <option name="hello">world</option>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'bar')
            ->assertOption('baz', 'qux')
            ->assertOptionMissing('hello');
    }

    public function testConfigFallbackToRootWhenOneMatchEmpty()
    {
        $this->givenUrlRules('
            <rule match="empty"></rule>
            <rule default="true">
                <option name="foo">bar</option>
            </rule>')
            ->andUrl('http://test/empty/fluff')
            ->assertOption('foo', 'bar');
    }

    public function testConfigFallbackToRootDefault()
    {
        $this->givenUrlRules('
            <rule match="bar">
                <rule match="foo"></rule>
            </rule>
            <rule default="true">
                <option name="foo">bar</option>
            </rule>')
            ->andUrl('http://test/empty/fluff')
            ->assertOption('foo', 'bar');
    }

    public function testConfigFallbackToDefaultSite()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <option name="foo">bar</option>
            </rule>')
            ->andUrl('http://something.random/foo')
            ->assertOption('foo', 'bar');
    }

    public function testMatchWithPlusInItWorks()
    {
        $this->givenUrlRules('
            <rule match="1+2">
                <option name="foo">bar</option>
            </rule>')
            ->andUrl('http://test/1+2')
            ->assertOption('foo', 'bar');
    }

    public function testMatchWithMinusInItWorks()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <rule pattern="/^[a-z\-]+$/" store="category">
                    <option name="foo">bar</option>
                </rule>
            </rule>')
            ->andUrl('http://test/foo/hifi-produkter')
            ->assertUrlVariable('category', 'hifi-produkter')
            ->assertOption('foo', 'bar');
    }

    public function testConfigReset()
    {
        $this
            ->givenUrlRules('<rule match="foo"><option name="foo">bar</option></rule>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'bar');

        $this->aetherConfig->resetRuleConfig();

        $this->assertOptionMissing('foo');

        $this->assertNull($this->aetherConfig->getSection());
    }

    public function testTriggeredFallbackToDefaultRule()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <section>FooSection</section>
                <rule match="bar"></rule>
                <rule default="true">
                    <section>NotFoundSection</section>
                </rule>
            </rule>')
            ->andUrl('http://test/foo/bar')
            ->assertSection('FooSection');

        $this->aetherConfig->reloadConfigFromDefaultRule();

        $this->assertSection('NotFoundSection');
    }

    public function testBooleanTypeCasting()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <option name="isTrue" type="bool">true</option>
                <option name="isFalse" type="bool">false</option>
                <option name="isTrueString">true</option>
                <option name="isFalseString">false</option>
                <module>
                    <option name="isTrue" type="bool">true</option>
                    <option name="isFalse" type="bool">false</option>
                    <option name="isTrueString">true</option>
                    <option name="isFalseString">false</option>
                    FooModule
                </module>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertOption('isTrue', true)
            ->assertOption('isFalse', false)
            ->assertOption('isTrueString', 'true')
            ->assertOption('isFalseString', 'false')
            ->assertModuleOption('FooModule', 'isTrue', true)
            ->assertModuleOption('FooModule', 'isFalse', false)
            ->assertModuleOption('FooModule', 'isTrueString', 'true')
            ->assertModuleOption('FooModule', 'isFalseString', 'false');
    }

    public function testItIncludesImportNodes()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <option name="foo">bar</option>
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '
            <option name="importedOption">yup</option>
            <import>b</import>')
            ->withoutConfigFile('prod.a.xml')
            ->withoutConfigFile('test.a.xml')
            ->withConfigFile('b.xml', '
            <option name="nestedImportedOption">hell yeah</option>')
            ->withoutConfigFile('prod.b.xml')
            ->withoutConfigFile('test.b.xml')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'bar')
            ->assertOption('importedOption', 'yup')
            ->assertOption('nestedImportedOption', 'hell yeah');
    }

    public function testItIncludesImportNodesBasedOnLocalEnvironment()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '<option name="foo">a-base</option>')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'test-a');
    }

    public function testItIncludesTheUnprefixedFileInProduction()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '<option name="foo">a-base</option>')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'a-base');
    }

    public function testItIncludesTheProdPrefixedFileInProductionWhenThereIsNoUnprefixedFile()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withoutConfigFile('a.xml')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'prod-a');
    }

    public function testPageAndModuleCachingInProduction()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo" cache="10">
                <module cache="20">
                    TestModule
                </module>
            </rule>')
            ->andUrl('http://test/foo');

        $this->assertEquals(10, (int) $this->aetherConfig->getCacheTime());

        $this->assertEquals(20, (int) $this->aetherConfig->getModules()['TestModule']['cache']);
    }

    public function testCacheAttributesAreRemovedInLocalEnvironments()
    {
        $this->givenUrlRules('
            <rule match="foo" cache="10">
                <module cache="20">
                    TestModule
                </module>
            </rule>')
            ->andUrl('http://test/foo');

        $this->assertFalse($this->aetherConfig->getCacheTime());

        $this->assertArrayNotHasKey('cache', $this->aetherConfig->getModules()['TestModule']);
    }

    protected function withConfigFile($file, $rawXml)
    {
        $rawXml = "<config>{$rawXml}</config>";

        $path = "/config/./{$file}";

        $this->aetherConfigFiles->allows()->exists($path)->andReturn(true);
        $this->aetherConfigFiles->allows()->get($path)->andReturn($rawXml);

        return $this;
    }

    protected function withoutConfigFile($file)
    {
        $path = "/config/./{$file}";

        $this->aetherConfigFiles->allows()->exists($path)->andReturn(false);

        return $this;
    }

    protected function givenConfig($rawXml)
    {
        $this->aetherConfigFiles = m::mock(Filesystem::class);

        $this->aetherConfigFiles->shouldReceive('get')->with('/config/aether.config.xml')->andReturn($rawXml);

        $this->aetherConfig = new AetherConfig(
            '/config/aether.config.xml',
            $this->aetherConfigFiles
        );

        return $this;
    }

    protected function andUrl($url)
    {
        $this->aetherConfig->matchUrl(tap(new UrlParser)->parse($url));

        return $this;
    }

    protected function assertSection($expected)
    {
        $actual = $this->aetherConfig->getSection();

        Assert::assertEquals(
            $expected,
            $actual,
            "Section [{$actual}] is not an instance of [{$expected}]"
        );

        return $this;
    }

    protected function assertModule($module)
    {
        $modules = $this->aetherConfig->getModules();

        Assert::assertArrayHasKey(
            $module,
            $modules,
            "Module [{$module}] is missing"
        );

        return $this;
    }

    protected function assertModuleOption($module, $option, $value = null)
    {
        $options = $this->aetherConfig->getModules()[$module]['options'];

        Assert::assertArrayHasKey(
            $option,
            $options,
            "Module option [{$module}]->[{$option}] is missing"
        );

        if (func_num_args() > 1) {
            Assert::assertEquals(
                $value,
                $options[$option],
                "Module option [{$module}]->[{$option}] does not equal [{$value}]. Got [{$options[$option]}] instead"
            );
        }

        return $this;
    }

    protected function assertOption($option, $value = null)
    {
        $options = $this->aetherConfig->getOptions();

        Assert::assertArrayHasKey(
            $option,
            $options,
            "Option [{$option}] is missing"
        );

        if (func_num_args() > 1) {
            Assert::assertEquals(
                $value,
                $options[$option],
                "Option [{$option}] does not equal [{$value}]. Got [{$options[$option]}] instead"
            );
        }

        return $this;
    }

    protected function assertOptionMissing($option)
    {
        $options = $this->aetherConfig->getOptions();

        Assert::assertArrayNotHasKey(
            $option,
            $options,
            "Option [{$option}] is not missing"
        );

        return $this;
    }

    protected function assertUrlVariable($variable, $value = null)
    {
        Assert::assertTrue(
            $this->aetherConfig->hasUrlVar($variable),
            "URL Variable [{$variable}] is missing"
        );

        if (func_num_args() > 1) {
            $actual = $this->aetherConfig->getUrlVariable($variable);

            Assert::assertEquals(
                $value,
                $actual,
                "URL Variable [{$variable}] does not equal [{$value}]. Got [{$actual}] instead"
            );
        }

        return $this;
    }

    protected function givenUrlRules($xmlString, $site = '*')
    {
        return $this->givenConfig("<config><site name=\"{$site}\"><urlRules>{$xmlString}</urlRules></site></config>");
    }
}
