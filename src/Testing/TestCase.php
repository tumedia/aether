<?php

namespace Aether\Testing;

use Aether\Aether;
use Aether\UrlParser;
use Aether\Http\Kernel as HttpKernel;
use Aether\AetherConfig;
use Aether\Console\Kernel as ConsoleKernel;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $aether;

    protected $response;

    abstract protected function createAether();

    protected function setUp()
    {
        $this->aether = $this->createAether();

        $this->aether->make(ConsoleKernel::class)->bootstrap();
    }

    protected function tearDown()
    {
        $this->aether = null;

        Aether::setInstance(null);
    }

    /**
     * "Visit" the URL.
     *
     * @return \Aether\Testing\TestResponse
     */
    protected function visit($url = null)
    {
        if (! is_null($url)) {
            $this->setUrl($url);
        }

        $parsedUrl = new UrlParser;
        $parsedUrl->parse($url);

        if ($parsedUrl->get('query')) {
            parse_str($parsedUrl->get('query'), $_GET);
        }

        $response = $this->aether->make(HttpKernel::class)->handle($parsedUrl);

        return new TestResponse($this->aether, $response);
    }

    /**
     * Set the requested url bla bla bla derp.
     *
     * @param  string  $url
     * @return void
     */
    protected function setUrl($url)
    {
        $this->aether->singleton('parsedUrl', function () use ($url) {
            $aetherUrl = new UrlParser;

            $aetherUrl->parse($url);

            return $aetherUrl;
        });

        // Make sure to match the URL with the Aether config so everything's
        // up-to-date...
        $this->aether['aetherConfig']->matchUrl($this->aether['parsedUrl']);
    }
}
