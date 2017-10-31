<?php

namespace Tests;

use AetherJSONResponse;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testResponse()
    {
        $response = new AetherJSONResponse([
            'foo'  => 'bar',
            ' bar' => 'foo',
        ]);

        $expected = '{"foo":"bar"," bar":"foo"}';

        $this->assertEquals($expected, json_encode($response->get()));
    }
}
