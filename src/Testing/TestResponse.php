<?php

namespace Aether\Testing;

use Aether\Aether;
use ErrorException;
use RuntimeException;
use Illuminate\Support\Str;
use Aether\Response\Response;
use PHPUnit\Framework\Assert;

class TestResponse
{
    protected $aether;

    protected $response;

    protected $body;

    protected $headers;

    // todo: add assertions

    public function __construct(Aether $aether, Response $response)
    {
        $this->aether = $aether;
        $this->response = $response;

        $this->drawResponse();
    }

    /**
     * Get the body contents of the response.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Assert that the response body contains a given string.
     *
     * @param  string  $needle
     * @return \Aether\Testing\TestResponse  $this
     */
    public function assertSee($needle)
    {
        Assert::assertContains(
            $needle,
            $this->getBody(),
            "Response does not contain [{$needle}]"
        );

        return $this;
    }

    /**
     * Assert that a given header field has been set by the response.
     * If a value is specified, the content of the header field and the value
     * will be compared.
     *
     * @param  string  $header
     * @param  ?string  $value
     * @return \Aether\Testing\TestResponse  $this
     */
    public function assertHeader($header, $value = null)
    {
        Assert::assertArrayHasKey(
            $header,
            $this->headers,
            "Response header [{$header}] is not present"
        );

        if (! is_null($value)) {
            Assert::assertEquals(
                $value,
                $this->headers[$header],
                "Response header [{$header}] does not equal [{$value}]. Got [{$this->headers[$header]}] instead"
            );
        }

        return $this;
    }

    /**
     * Assert that a given header field has not been set by the response.
     *
     * @param  string  $header
     * @return \Aether\Testing\TestResponse  $this
     */
    public function assertHeaderMissing($header)
    {
        Assert::assertArrayNotHasKey(
            $header,
            $this->headers,
            "Response header [{$header}] is present"
        );

        return $this;
    }

    /**
     * Assert that the HTTP status code returned by the response matches the
     * given value.
     *
     * @param  int  $code
     * @return \Aether\Testing\TestResponse  $this
     */
    public function assertStatus($code)
    {
        $actual = http_response_code();

        if ($actual === false) {
            throw new ErrorException('Could not determine response status code');
        }

        Assert::assertEquals(
            $code,
            $actual,
            "Response status [{$actual}] does not equal [{$code}]"
        );

        return $this;
    }

    /**
     * "Draw" the response and set local properties.
     *
     * @return void
     */
    protected function drawResponse()
    {
        try {
            $this->body = $this->silently(function () {
                $this->response->draw($this->aether);
            });

            $this->headers = $this->getSentHeaders();

            return $this;
        } catch (ErrorException $e) {
            if ($this->isModifiedHeaderErrorException($e)) {
                $e = new ErrorException('Caught modified header information exception. SOLUTION: Add @runInSeparateProcess to your test method\'s doc comments.');
            }

            throw $e;
        }
    }

    /**
     * Get the headers that have been set by the response.
     * Note that this requires xdebug to be installed.
     *
     * @return array
     */
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

    /**
     * Determine if a given ErrorException was triggered by attempting to set
     * headers after they have been sent.
     *
     * @param  \ErrorException  $e
     * @return bool
     */
    protected function isModifiedHeaderErrorException(ErrorException $e)
    {
        return Str::startsWith(
            $e->getMessage(),
            'Cannot modify header information - headers already sent by '
        );
    }

    /**
     * Return output generated by a given callback, instead of outputting it.
     *
     * @param  callable  $callback
     * @return string
     */
    protected function silently($callback)
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }
}
