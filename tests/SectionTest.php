<?php

class AetherSectionTest extends PHPUnit_Framework_TestCase
{
    private function getConfig()
    {
        return new AetherConfig(__DIR__.'/fixtures/aether.config.xml');
    }

    private function getLoadedConfig($url)
    {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);

        return $conf;
    }

    public function testSectionCan404()
    {
        $sl = new AetherServiceLocator;
        $config = $this->getLoadedConfig('http://raw.no/unittest/goodtimes/nay');
        $sl->set('aetherConfig', $config);

        $section = AetherSectionFactory::create(
            'Testsection',
            $sl
        );

        $response = $section->response();
        $this->assertTrue($response instanceof AetherTextResponse);
        $this->assertEquals('404 Eg fant han ikkje', $response->get(), 'Response should be NotFoundSection\'s output');
        $this->assertArrayNotHasKey('id', $response->options, 'Options should be cleared when reloading config');
    }
}
