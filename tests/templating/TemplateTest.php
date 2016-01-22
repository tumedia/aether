<?php // 
require_once(AETHER_PATH . 'lib/AetherServiceLocator.php');
/**
 * 
 * Test basic templating facade
 * 
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package
 */
class AetherTemplateTest extends PHPUnit_Framework_TestCase {
    public function testGetTemplateObject() {
        $sl = new AetherServiceLocator;
        $sl->set('projectRoot', AETHER_PATH . 'tests/templating/');

        $config = new AetherConfig('./aether.config.xml');
        $sl->set('aetherConfig', $config);

        $tpl = $sl->getTemplate();
        $this->assertTrue($tpl instanceof AetherTemplateSmarty);
    }
}
