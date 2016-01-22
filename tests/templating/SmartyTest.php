<?php // 
require_once('PHPUnit/Autoload.php');
require_once(AETHER_PATH . 'lib/AetherServiceLocator.php');
/**
 * 
 * THIS IS NOT A UNIT TEST
 * Verify that Smarty integrates into Aether and is useable
 * 
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package aether.test
 */

class SmartyIntegratesWithAetherTest extends PHPUnit_Framework_TestCase {
    public function testGetSmartyEngine() {
        return $this->markTestIncomplete(
            'This is supposedly not a test.'
        );

        // Go through SL
        $sl = new AetherServiceLocator;
        // TODO THIS IS UGLY AND MUST BE BAD
        $sl->set('projectRoot', AETHER_PATH . 'tests/templating/');
        // Fetch smarty
        $tpl = $sl->getTemplate();
        $tpl->set('foo',array('a'=>'hello','b'=>'world'));
        $out = $tpl->fetch('test.tpl');
        $this->assertTrue(substr_count($out,'hello world') > 0);
    }
}
