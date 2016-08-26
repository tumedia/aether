<?php //

class SmartyIntegratesWithAetherTest extends PHPUnit_Framework_TestCase {
    public function testGetSmartyEngine() {
        return $this->markTestIncomplete(
            'This is supposedly not a test.'
        );

        // Go through SL
        $sl = new AetherServiceLocator;
        // TODO THIS IS UGLY AND MUST BE BAD
        $sl->set('projectRoot', __DIR__.'/templating/');
        // Fetch smarty
        $tpl = $sl->getTemplate();
        $tpl->set('foo',array('a'=>'hello','b'=>'world'));
        $out = $tpl->fetch('test.tpl');
        $this->assertTrue(substr_count($out,'hello world') > 0);
    }
}
