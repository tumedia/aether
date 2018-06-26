<?php

namespace Tests\Response;

use PHPUnit\Framework\TestCase;
use Aether\Response\JsonCommentFiltered;

class JsonCommentFilteredResponseTest extends TestCase
{
    public function testResponse()
    {
        $response = new JsonCommentFiltered([
            'foo' => 'bar',
            ' bar' => 'foo',
        ]);

        $out = $response->get();

        $this->assertTrue(strpos($out, '{"foo":"bar"," bar":"foo"}') !== false);

        $this->assertTrue(preg_match('/\/\*[^\*]+\*\//', $out) == true);
    }
}
