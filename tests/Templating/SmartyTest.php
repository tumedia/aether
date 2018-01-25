<?php

namespace Tests\Templating;

use Aether\Aether;
use Tests\TestCase;

class SmartyTest extends TestCase
{
    public function testGetSmartyEngine()
    {
        $tpl = $this->getTemplateEngine(['foo' => [
            'a' => 'hello',
            'b' => 'world',
        ]]);

        $this->assertContains('hello world', $tpl->fetch('test.tpl'));
    }

    public function testTemplateExists()
    {
        $tpl = $this->getTemplateEngine();

        $this->assertTrue($tpl->templateExists('test.tpl'));
        $this->assertFalse($tpl->templateExists('martin.tpl'));
    }

    protected function tearDown()
    {
        array_map('unlink', glob(dirname(__DIR__).'/Fixtures/templates/compiled/*.php'));
    }

    private function getTemplateEngine(array $data = [])
    {
        $this->setUrl('/');

        $tpl = $this->aether->getTemplate();

        foreach ($data as $key => $value) {
            $tpl->set($key, $value);
        }

        return $tpl;
    }
}
