<?php // vim:set ts=4 sw=4 et:

if (!defined('AETHER_PATH'))
    define('AETHER_PATH', __DIR__ . '/../');

require_once(AETHER_PATH . 'lib/AetherConfig.php');
require_once(AETHER_PATH . 'lib/AetherUrlParser.php');
require_once(AETHER_PATH . 'lib/AetherExceptions.php');

/**
 * 
 * Created: 2009-02-17
 * @author Raymond Julin
 * @package aether.test
 */

class AetherConfigTest extends PHPUnit_Framework_TestCase {
    private function getConfig() {
        return new AetherConfig(AETHER_PATH . 'tests/fixtures/aether.config.xml');
    }

    public function testEnvironment() {
        $this->assertTrue(class_exists('AetherConfig'));
    }

    private function getLoadedConfig($url) {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);

        return $conf;
    }

    private function getOptionsForUrl($url) {
        $config = $this->getLoadedConfig($url);
        return $config->getOptions();
    }

    public function testConfigReadDefault() {
        $url = 'http://raw.no/unittest';
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $this->assertEquals($conf->getSection(), 'Generic');
    }

    public function testConfigReadDefaultBase() {
        $url = 'http://raw.no/fluff';
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $opts = $conf->getOptions();
        $this->assertEquals($conf->getSection(), 'Generic');
        $this->assertEquals($opts['foobar'], 'yes');
    }

    public function testConfigAssembleOptionsCorrectly() {
        $url = 'http://raw.no/unittest/foo';
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $modules = $conf->getModules();

        // Check the module exists
        $this->assertTrue(isset($modules['HelloWorld']));

        $module = $modules['HelloWorld'];

        // Check the local options for the HelloWorld module
        $this->assertEquals($module['options']['foo'], 'foobar');
    }

    public function testMultipleModulesOfSameType() {
        $url = 'http://raw.no/tema/Playstation 3';
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $modules = $conf->getModules();
        // Check options against the first module
        $this->assertTrue(is_array($modules));
    }

    public function testConfigFindParentDefault() {
        $aetherUrl = new AetherUrlParser;
        // Second
        $url = 'http://raw.no/thisshouldgive404';
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $opts = $conf->getOptions();
        $this->assertEquals($opts['foobar'], 'yes');
        // Third
        $url = 'http://raw.no/unittest/heisann00';
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $opts = $conf->getOptions();
        $this->assertEquals($opts['def'], 'yes');
    }

    public function testConfigFallbackToRootWhenOneMatchEmpty() {
        $opts = $this->getOptionsForUrl('http://raw.no/empty/fluff');
        $this->assertEquals('yes', $opts['foobar']);
    }
    
    public function testConfigFallbackToRootDefault() {
        $opts = $this->getOptionsForUrl('http://raw.no/bar/foo/bar');

        $this->assertEquals('yes', $opts['foobar']);
    }

    public function testConfigFallbackToDefaultSite() {
        $aetherUrl = new AetherUrlParser;
        $url = 'http://foo.no/unittest';
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $options = $conf->getOptions();
        $this->assertEquals($options['sitename'], 'fallback-site');
    }

    public function testMatchWithPlusInItWorks() {
        $aetherUrl = new AetherUrlParser;
        $url = 'http://raw.no/unittest/foo/a+b';
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $opts = $conf->getOptions();
        $this->assertEquals('yes', $opts['plusm']);
    }

    public function testMatchWithMinusInItWorks() {
        $aetherUrl = new AetherUrlParser;
        $cat = "hifi-produkter";
        $url = 'http://raw.no/unittest/' . $cat;
        $aetherUrl->parse($url);
        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);
        $this->assertEquals($conf->getUrlVariable('catName'), $cat);
    }
}
