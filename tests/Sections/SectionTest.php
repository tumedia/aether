<?php

namespace Tests\Sections;

use Tests\TestCase;

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
            ->assertStatus(404)
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
            ->assertStatus(200)
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
            ->assertStatus(200)
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
            ->assertStatus(200)
            ->assertHeaderMissing('Cache-Control');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSecionsShouldProvideModulesToTheTemplate()
    {
        $this
            ->visit('http://raw.no/providing-modules/foo')
            ->assertStatus(200)
            ->assertSee('Hello local');
    }
}
