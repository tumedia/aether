<?php

namespace Tests;

use Aether\UrlParser;
use Aether\AetherConfig;
use Aether\Response\Text;
use Aether\ServiceLocator;
use Aether\Sections\SectionFactory;
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

        $sl = new ServiceLocator;
        $config = $this->getLoadedConfig('');
        $sl->set('aetherConfig', $config);

        $section = SectionFactory::create(
            Testsection::class,
            $sl
        );

        $response = $section->response();
        $this->assertInstanceOf(Text::class, $response);
        $this->assertEquals('404 Eg fant han ikkje', $response->get(), 'Response should be NotFoundSection\'s output');
        $this->assertArrayNotHasKey('id', $response->options, 'Options should be cleared when reloading config');
    }

    private function getLoadedConfig($url)
    {
        $aetherUrl = new UrlParser;
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
