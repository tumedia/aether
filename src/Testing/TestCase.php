<?php

namespace Aether\Testing;

use Aether\Aether;
use Aether\UrlParser;
use Aether\AetherConfig;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $aether;

    protected $response;

    abstract protected function createAether();

    protected function setUp()
    {
        $this->aether = $this->createAether();
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

        $parsedUrl = $this->aether['parsedUrl'];

        if ($parsedUrl->get('query')) {
            parse_str($parsedUrl->get('query'), $_GET);
        }

        $this->response = new TestResponse($this->aether);

        return $this->response->generate();
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
        $this->aether[AetherConfig::class]->matchUrl($this->aether['parsedUrl']);
    }
}
