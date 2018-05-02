<?php

namespace Tests\Filesystem;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class FilesystemTest extends TestCase
{
    public function testItIsBoundToTheFilesKeyword()
    {
        $this->assertInstanceOf(Filesystem::class, $this->aether['files']);
    }
}
