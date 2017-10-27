<?php //

class SmartyIntegratesWithAetherTest extends PHPUnit_Framework_TestCase
{
    public function getTemplateEngine()
    {
        // Go through SL
        $sl = new AetherServiceLocator;
        $sl->set('projectRoot', __DIR__ . '/');

        $config = new AetherConfig('./aether.config.xml');
        $sl->set('aetherConfig', $config);

        // Fetch smarty
        return $sl->getTemplate();
    }

    public function testGetSmartyEngine()
    {
        $tpl = $this->getTemplateEngine();
        $tpl->set('foo', array('a'=>'hello','b'=>'world'));
        $out = $tpl->fetch('test.tpl');
        $this->assertTrue(substr_count($out, 'hello world') > 0);
    }

    public function testTemplateExists()
    {
        $tpl = $this->getTemplateEngine();
        $this->assertTrue($tpl->templateExists('test.tpl'));
        $this->assertFalse($tpl->templateExists('martin.tpl'));
    }

    public function tearDown()
    {
        array_map('unlink', glob(__DIR__ . '/templates/compiled/*.php'));
    }
}
