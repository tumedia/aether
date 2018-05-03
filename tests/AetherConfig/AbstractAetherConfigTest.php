<?php

namespace Tests\AetherConfig;

use Mockery as m;
use Aether\Aether;
use Aether\UrlParser;
use Aether\AetherConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class AbstractAetherConfigTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $aetherConfig;

    protected $aetherConfigFiles;

    protected $aetherConfigOptions;

    protected function tearDown()
    {
        Aether::setInstance(null);
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
        return $this->withUrl($url);
    }

    protected function withUrl($url)
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
