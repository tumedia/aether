<?php // vim:set ts=4 sw=4 et:

class TestCLIApp extends AetherCLI {
    protected $allowedOptions = array(
        'f' => 'foo',
        'm' => 'meh'
    );
    public function getOptions($args) {
        $this->options = $this->parseOptions($args);
        return $this->options;
    }
    public function run() {
    }
    public function getTime() {
        return $this->getRunTime();
    }
}
class TestCLIApp2 extends TestCLIApp {
    protected $allowedOptions = array();
}
class AetherCLITest extends PHPUnit_Framework_TestCase {
    public function testParseOptions() {
        $_SERVER['argv'] = array($_SERVER['argv'][0], '--foo=bar');

        ob_start();
        $app = new TestCLIApp;
        $out = ob_get_clean();

        // Pass #0 Long opts with filename.php
        $opts = $app->getOptions(array("filename.php", "--foo=bar"));
        $arr = array('foo'=>'bar');
        $this->assertEquals($arr, $opts);

        // Pass #1 Long opts
        $opts = $app->getOptions(array("--foo=bar"));
        $arr = array('foo'=>'bar');
        $this->assertEquals($arr, $opts);

        // Pass #2 Long opts
        $opts = $app->getOptions(array("--foo=bar", "--meh=eh"));
        $arr = array('foo'=>'bar','meh'=>'eh');
        $this->assertEquals($arr, $opts);

        // Pass #3 Long opts, illegal option
        $opts = $app->getOptions(array("--foo=bar", "--bleh=eh"));
        $arr = array('foo'=>'bar');
        $this->assertEquals($arr, $opts);

        // Pass #4 Short opts
        $opts = $app->getOptions(array("-f=bar", "-m=eh"));
        $arr = array('foo'=>'bar', 'meh'=>'eh');
        $this->assertEquals($arr, $opts);

        // Pass #5 Short opts, illegal options
        $opts = $app->getOptions(array("-t=bar", "-m=eh"));
        $arr = array('meh'=>'eh');
        $this->assertEquals($arr, $opts);

        // Pass #6 Fetch option one by one
        $app->getOptions(array("--meh=eh"));
        //$arr = array('meh'=>'eh');
        $this->assertEquals($app->getOption('meh'), 'eh');
    }

    public function testHasOptions() {
        $_SERVER['argv'] = array($_SERVER['argv'][0], '--foo=bar');
        ob_start();
        $app = new TestCLIApp;
        $opts = $app->getOptions(array("filename.php", "--foo=bar"));
        $arr = array('foo'=>'bar');
        $this->assertEquals($arr, $opts);
        $this->assertTrue($app->hasOptions(array('foo')));
        $this->assertFalse($app->hasOptions(array('foo','bar')));
        $out = ob_get_clean();
    }

    public function testHelpMixin() {
        ob_start();
        $_SERVER['argv'] = array($_SERVER['argv'][0], '--foo=bar');
        $app = new TestCLIApp;
        $this->assertEquals($app->getOption('h'), '');
        $arr = array('help'=>'');
        $opts = $app->getOptions(array("--help"));
        $out = ob_get_clean();
        $this->assertEquals($arr, $opts);
    }

    public function testDisplayHelp() {
        return $this->markTestIncomplete('Disabled due to riskyness.');
        ob_start();
        $app = new TestCLIApp2;
        $out = ob_get_clean();
        $this->assertTrue(strpos($out, 'default help file') > 0);
    }

    public function testAutoTiming() {
        return $this->markTestIncomplete('Disabled due to riskyness.');
        ob_start();
        $app = new TestCLIApp;
        $app->run();
        $out = ob_get_clean();
        $this->assertEquals(strpos($out, 'Start time'),
            0, 'Start missing in timing');
        #$this->assertEquals(strpos($out, 'End time'),
            #0, 'End missing in timing');
        $t1 = $app->getTime();
        sleep(1);
        $t2 = $app->getTime();
        $this->assertTrue($t2 > $t1);
    }
}
