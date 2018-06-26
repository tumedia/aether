<?php

namespace Tests\AetherConfig;

class AetherConfigTest extends AbstractAetherConfigTest
{
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

        $this->assertNull($this->aetherConfig->getUrlVar('random'));
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

    public function testPageAndModuleCachingInProduction()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo" cache="10" cacheas="foo-key">
                <module cache="20" cacheas="module-key">
                    TestModule
                </module>
            </rule>')
            ->andUrl('http://test/foo');

        $this->assertEquals(10, (int) $this->aetherConfig->getCacheTime());

        $this->assertEquals('foo-key', $this->aetherConfig->getCacheName());

        $this->assertEquals(20, (int) $this->aetherConfig->getModules()['TestModule']['cache']);
        $this->assertEquals('module-key', $this->aetherConfig->getModules()['TestModule']['cacheas']);
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

    public function testModuleProvidesAttribute()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <module provides="foo">
                    FooModule
                </module>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertModule('foo');
    }

    public function testOptionAddMode()
    {
        $this->givenUrlRules('
            <option name="foo">a</option>
            <rule match="add-b">
                <option name="foo" mode="add">b</option>
                <rule match="add-d">
                    <option name="foo" mode="add">d</option>
                </rule>
            </rule>
            <rule default="true"></rule>');

        $this->withUrl('http://test/')->assertOption('foo', 'a');

        $this->withUrl('http://test/add-b')->assertOption('foo', 'a;b');

        $this->withUrl('http://test/add-b/add-d')->assertOption('foo', 'a;b;d');
    }

    public function testOptionDeleteMode()
    {
        $this->givenUrlRules('
            <option name="foo">a</option>
            <rule match="add-b">
                <option name="foo" mode="add">b</option>
                <rule match="delete-b">
                    <option name="foo" mode="del">b</option>
                </rule>
            </rule>');

        $this->withUrl('http://test/add-b')->assertOption('foo', 'a;b');

        $this->withUrl('http://test/add-b/delete-b')->assertOption('foo', 'a');
    }

    public function testGetModulesAcceptsProviderName()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <module provides="bar">BarModule</module>
                <module provides="baz">BazModule</module>
            </rule>')
            ->andUrl('http://test/foo');

        $this->assertArraySubset(
            ['provides' => 'bar', 'name' => 'BarModule'],
            $this->aetherConfig->getModules('bar')
        );

        $this->assertNull($this->aetherConfig->getModules('random'));
    }

    public function testSetOptionMethod()
    {
        $this->givenUrlRules('');

        $this->aetherConfig->setOption('foo', 'bar');

        $this->assertOption('foo', 'bar');
    }
}
