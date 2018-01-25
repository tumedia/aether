<?php

namespace Tests\Templating;

use AetherConfig;
use Aether\Aether;
use Tests\TestCase;
use AetherServiceLocator;
use AetherTemplateSmarty;

class TemplateTest extends TestCase
{
    public function testGetTemplateObject()
    {
        $this->setUrl('/');

        $this->assertInstanceOf(
            AetherTemplateSmarty::class,
            $this->aether->getTemplate()
        );
    }
}
