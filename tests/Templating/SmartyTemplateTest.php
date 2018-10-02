<?php

namespace Tests\Templating;

use Smarty;
use Mockery as m;
use Tests\TestCase;
use Aether\Templating\Template;
use Aether\Templating\SmartyTemplate;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Aether\Templating\NamespacedTemplatesSmartyResource;

class SmartyTemplateTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetSmartyEngine()
    {
        $tpl = $this->aether->getTemplate();

        $tpl->set('foo', [
            'a' => 'hello',
            'b' => 'world',
        ]);

        $this->assertContains('hello world', $tpl->fetch('test.tpl'));
    }

    public function testSetAllMethod()
    {
        $tpl = $this->aether->getTemplate();

        $tpl->setAll(['foo' => [
            'a' => 'hello',
            'b' => 'world',
        ]]);

        $this->assertContains('hello world', $tpl->fetch('test.tpl'));
    }

    public function testHasVariable()
    {
        $tpl = $this->aether->getTemplate();

        $this->assertFalse($tpl->hasVariable('foo'));

        $tpl->set('foo', 'yes hello');

        $this->assertTrue($tpl->hasVariable('foo'));
    }

    public function testTemplateExists()
    {
        $tpl = $this->aether->getTemplate();

        $this->assertTrue($tpl->templateExists('test.tpl'));
        $this->assertFalse($tpl->templateExists('martin.tpl'));
    }

    public function testTemplateMethodReturnsTemplateInstance()
    {
        $this->assertInstanceOf(Template::class, \template());
    }

    public function testTemplateMethodReturnsRenderedTemplate()
    {
        $this->assertEquals(" \n", \template('test.tpl'));

        $rendered = \template('test.tpl', ['foo' => [
            'a' => 'lorem',
            'b' => 'ipsum',
        ]]);

        $this->assertContains('lorem ipsum', $rendered);
    }

    public function testTheTemplateClassIsMacroable()
    {
        Template::macro('addOne', function ($value) {
            return $value + 1;
        });

        $template = resolve('template');

        $this->assertEquals(2, $template->addOne(1));
    }

    public function testAddingPaths()
    {
        $template = resolve('template');

        $template->addPath(__DIR__.'/fixtures/package/templates');

        $this->assertTrue($template->templateExists('from-added-path.tpl'));
    }

    public function testAddingNamespacedPath()
    {
        $template = resolve('template');
        $template->addNamespace(__DIR__.'/fixtures/package/templates', 'foo');

        $this->assertContains('it works', $template->fetch('foo:from-added-path.tpl'));
        $this->assertTrue($template->templateExists('foo:from-added-path.tpl'));
        $this->assertFalse($template->templateExists('from-added-path.tpl'));
    }

    public function testNamespacedTemplatesCanBeLocallyOverridden()
    {
        $this->aether->instance('projectRoot', __DIR__.'/fixtures/app/');

        $template = resolve('template');
        $template->addNamespace(__DIR__.'/fixtures/package/templates', 'foo');

        $this->assertContains('overridden', $template->fetch('foo:from-added-path.tpl'));
    }

    public function testNamespaceOverridesCanExtendThemselvesByForceUsingTheOriginalTemplate()
    {
        $this->aether->instance('projectRoot', __DIR__.'/fixtures/app/');

        $template = resolve('template');
        $template->addNamespace(__DIR__.'/fixtures/package/templates', 'foo');

        $this->assertContains('overridden', $template->fetch('foo:extends-self.tpl'));
    }

    public function testNamespaceOverridesCanIncludeThemselvesByForceUsingTheOriginalTemplate()
    {
        $this->aether->instance('projectRoot', __DIR__.'/fixtures/app/');

        $template = resolve('template');
        $template->addNamespace(__DIR__.'/fixtures/package/templates', 'foo');

        $this->assertContains('<div>hello from package', $template->fetch('foo:includes-self.tpl'));
    }

    public function testAddingSearchpathToTheTemplatePaths()
    {
        $this->aether['aetherConfig']->setOption('searchpath', 'foo; ./bar');

        $smarty = m::spy(Smarty::class);
        $template = new SmartyTemplate($this->aether, $smarty);

        $smarty->shouldHaveReceived('addTemplateDir')->with('foo/templates');
        $smarty->shouldHaveReceived('addTemplateDir')->with("{$this->aether['projectRoot']}./bar/templates");

        $smarty->shouldHaveReceived('addPluginsDir')->with('foo/templates/plugins');
        $smarty->shouldHaveReceived('addPluginsDir')->with("{$this->aether['projectRoot']}./bar/templates/plugins");
    }

    public function testPluginRegistration()
    {
        $smarty = m::spy(Smarty::class);
        $template = new SmartyTemplate($this->aether, $smarty);

        $template->registerPlugin('function', 'foo', 'method');

        $smarty->shouldHaveReceived('registerPlugin')->with('function', 'foo', 'method');
    }

    protected function tearDown()
    {
        resolve('template')->clearCompiled();
    }
}
