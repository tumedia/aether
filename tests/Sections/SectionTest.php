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
    }

    /**
     * @runInSeparateProcess
     */
    public function testSectionCacheHeader()
    {
        config()->set('app.env', 'production');
        $this
            ->visit('http://raw.no/section-test/cache/me/if/you/can')
            ->assertHeader('Cache-Control', 's-maxage=30');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSectionCacheHeaderZero()
    {
        config()->set('app.env', 'production');
        $this
            ->visit('http://raw.no/section-test/cache/me/if/you/cannot')
            ->assertHeader('Cache-Control', 's-maxage=0');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSectionCacheHeaderMissing()
    {
        config()->set('app.env', 'production');
        $this
            ->visit('http://raw.no/section-test/missing-cache')
            ->assertHeaderMissing('Cache-Control');
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
