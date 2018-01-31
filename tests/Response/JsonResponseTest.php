<?php

namespace Tests;

use Aether\Response\Json;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testResponse()
    {
        $response = new Json([
            'foo'  => 'bar',
            ' bar' => 'foo',
        ]);

        $expected = '{"foo":"bar"," bar":"foo"}';

        $this->assertEquals($expected, json_encode($response->get()));
    }
}
