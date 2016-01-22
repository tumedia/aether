<?php

if (!defined('AETHER_PATH'))
    define('AETHER_PATH', __DIR__ . '/../');

require_once(AETHER_PATH . 'lib/AetherConfig.php');
require_once(AETHER_PATH . 'lib/AetherUrlParser.php');
require_once(AETHER_PATH . 'lib/AetherSection.php');

class AetherSectionTest extends PHPUnit_Framework_TestCase  {

    private function getConfig() {
        return new AetherConfig(AETHER_PATH . 'tests/fixtures/aether.config.xml');
    }

    private function getLoadedConfig($url) {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);

        return $conf;
    }

    public function testSectionCan404() {
        $sl = new AetherServiceLocator;
        $config = $this->getLoadedConfig('http://raw.no/unittest/goodtimes/nay');
        $sl->set('aetherConfig', $config);

        AetherSectionFactory::$strict = true;
        AetherSectionFactory::$path = __DIR__ . '/fixtures';
        $section = AetherSectionFactory::create(
            'Testsection',
            $sl
        );

        $response = $section->response();
        $this->assertTrue($response instanceof AetherTextResponse);
        $this->assertEquals('404 Eg fant han ikkje', $response->get());
    }

}
