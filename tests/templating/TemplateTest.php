<?php //

class AetherTemplateTest extends PHPUnit_Framework_TestCase {
    public function testGetTemplateObject() {
        $sl = new AetherServiceLocator;
        $sl->set('projectRoot', __DIR__.'/templating/');

        $config = new AetherConfig('./aether.config.xml');
        $sl->set('aetherConfig', $config);

        $tpl = $sl->getTemplate();
        $this->assertTrue($tpl instanceof AetherTemplateSmarty);
    }
}
