<?php

namespace Tests;

use AetherConfig;
use AetherUrlParser;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Sections\NotFoundSection;

class ConfigTest extends TestCase
{
    public function testConfigReadDefault()
    {
        $config = $this->getLoadedConfig('http://raw.no/unittest');
        $options = $config->getOptions();

        $this->assertEquals('Generic', $config->getSection());
        $this->assertEquals('yes', $options['def']);
    }

    public function testConfigReadDefaultBase()
    {
        $config = $this->getLoadedConfig('http://raw.no/fluff');
        $options = $config->getOptions();

        $this->assertEquals('Generic', $config->getSection());
        $this->assertEquals('yes', $options['foobar']);
    }

    public function testConfigAssembleOptionsCorrectly()
    {
        $config = $this->getLoadedConfig('http://raw.no/unittest/foo');

        $modules = $config->getModules();
        $this->assertArrayHasKey('HelloWorld', $modules, 'Module must exist');

        $module = $modules['HelloWorld'];
        $this->assertEquals('foobar', $module['options']['foo'], 'Module\'s local options must be correct');
    }

    public function testConfigFindParentDefault()
    {
        $firstOptions = $this->getOptionsForUrl('http://raw.no/thisshouldgive404');
        $this->assertEquals('yes', $firstOptions['foobar']);

        $secondOptions = $this->getOptionsForUrl('http://raw.no/unittest/heisann00');
        $this->assertEquals('yes', $secondOptions['def']);
    }

    public function testConfigFallbackToRootWhenOneMatchEmpty()
    {
        $options = $this->getOptionsForUrl('http://raw.no/empty/fluff');
        $this->assertEquals('yes', $options['foobar']);
    }

    public function testConfigFallbackToRootDefault()
    {
        $options = $this->getOptionsForUrl('http://raw.no/bar/foo/bar');

        $this->assertEquals('yes', $options['foobar']);
    }

    public function testConfigFallbackToDefaultSite()
    {
        $options = $this->getOptionsForUrl('http://foo.no/unittest');
        $this->assertEquals('fallback-site', $options['sitename']);
    }

    public function testMatchWithPlusInItWorks()
    {
        $options = $this->getOptionsForUrl('http://raw.no/unittest/foo/a+b');
        $this->assertEquals('yes', $options['plusm']);
    }

    public function testMatchWithMinusInItWorks()
    {
        $category = 'hifi-produkter';
        $config = $this->getLoadedConfig("http://raw.no/unittest/{$category}");
        $this->assertTrue($config->hasUrlVar('catName'));
        $this->assertEquals($category, $config->getUrlVariable('catName'));
    }

    public function testConfigReset()
    {
        $config = $this->getLoadedConfig("http://raw.no/unittest/goodtimes");
        $config->resetRuleConfig();

        $this->assertEmpty($config->getOptions());
        $this->assertNull($config->getSection());
    }

    public function testTriggeredFallbackToDefaultRule()
    {
        $config = $this->getLoadedConfig("http://raw.no/unittest/goodtimes/nay");
        $config->reloadConfigFromDefaultRule();

        $this->assertEquals(NotFoundSection::class, $config->getSection());
    }

    public function testBooleanTypeCasting()
    {
        $options = $this->getOptionsForUrl('http://foobar.com/bool-casting');

        $this->assertTrue($options['shouldBeTrue']);
        $this->assertFalse($options['shouldBeFalse']);

        $this->assertSame($options['shouldBeTrueString'], 'true');
        $this->assertSame($options['shouldBeFalseString'], 'false');
    }

    public function testBooleanTypeCastingInModules()
    {
        $config = $this->getLoadedConfig('http://foobar.com/bool-casting');
        $module = current($config->getModules());
        $options = $module['options'];

        $this->assertTrue($options['fisk']);
        $this->assertFalse($options['ananas']);
    }

    private function getConfig()
    {
        return new AetherConfig(__DIR__.'/Fixtures/aether.config.xml');
    }

    private function getLoadedConfig($url)
    {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $config = $this->getConfig();
        $config->matchUrl($aetherUrl);

        return $config;
    }

    private function getOptionsForUrl($url)
    {
        return $this->getLoadedConfig($url)->getOptions();
    }
}
