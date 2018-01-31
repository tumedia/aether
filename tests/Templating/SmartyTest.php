<?php

namespace Tests\Templating;

use Tests\TestCase;

class SmartyTest extends TestCase
{
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

    public function testTemplateExists()
    {
        $tpl = $this->aether->getTemplate();

        $this->assertTrue($tpl->templateExists('test.tpl'));
        $this->assertFalse($tpl->templateExists('martin.tpl'));
    }

    public function testSearchpathIsIncluded()
    {
        $this->setUrl('http://raw.no/searchpath-test');

        $tpl = $this->aether->getTemplate();

        $this->assertContains('Yay!', $tpl->fetch('searchpath-found.tpl'));
    }

    protected function tearDown()
    {
        array_map('unlink', glob(dirname(__DIR__).'/Fixtures/templates/compiled/*.php'));
    }
}
