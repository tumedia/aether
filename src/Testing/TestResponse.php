<?php

namespace Aether\Testing;

use Aether\Aether;
use RuntimeException;
use PHPUnit\Framework\Assert;

class TestResponse
{
    protected $aether;

    protected $body;

    public $headers;

    // todo: add assertions

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
    }

    /**
     * @internal
     */
    public function generate()
    {
        ob_start();

        $this->aether->render();

        $this->body = ob_get_contents();

        ob_end_clean();

        $this->headers = $this->getSentHeaders();

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function assertSee($needle)
    {
        Assert::assertContains(
            $needle,
            $this->body,
            "Response does not contain [{$needle}]"
        );

        return $this;
    }

    public function assertHeader($header, $value = null)
    {
        Assert::assertArrayHasKey(
            $header,
            $this->headers,
            "Response header [{$header}] is not present"
        );

        if (! is_null($value)) {
            Assert::assertEquals(
                $this->headers[$header],
                $value,
                "Response header [{$header}] does not equal [{$value}]. Got [{$this->headers[$header]}] instead"
            );
        }

        return $this;
    }

    protected function getSentHeaders()
    {
        if (! function_exists('xdebug_get_headers')) {
            throw new RuntimeException('Xdebug is required to assert response headers');
        }

        $headers = [];

        foreach (xdebug_get_headers() as $header) {
            list($name, $value) = explode(':', $header, 2);

            // todo: is this correct?
            $headers[rtrim($name, ' ')] = ltrim($value, ' ');
        }

        return $headers;
    }
}
