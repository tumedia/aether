<?php

namespace Tests;

use AetherConfig;
use AetherUrlParser;
use AetherTextResponse;
use AetherSectionFactory;
use AetherServiceLocator;
use Tests\Fixtures\Sections\Testsection;

class SectionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSectionCan404()
    {
        $this
            ->visit('http://raw.no/unittest/goodtimes/nay')
            ->assertSee('404 Eg fant han ikkje')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        return;

        $sl = new AetherServiceLocator;
        $config = $this->getLoadedConfig('');
        $sl->set('aetherConfig', $config);

        $section = AetherSectionFactory::create(
            Testsection::class,
            $sl
        );

        $response = $section->response();
        $this->assertInstanceOf(AetherTextResponse::class, $response);
        $this->assertEquals('404 Eg fant han ikkje', $response->get(), 'Response should be NotFoundSection\'s output');
        $this->assertArrayNotHasKey('id', $response->options, 'Options should be cleared when reloading config');
    }

    private function getLoadedConfig($url)
    {
        $aetherUrl = new AetherUrlParser;
        $aetherUrl->parse($url);

        $conf = $this->getConfig();
        $conf->matchUrl($aetherUrl);

        return $conf;
    }

    private function getConfig()
    {
        return new AetherConfig(__DIR__.'/Fixtures/config/aether.config.xml');
    }
}
