<?php

namespace Tests\Templating;

use AetherConfig;
use AetherServiceLocator;
use AetherTemplateSmarty;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testGetTemplateObject()
    {
        $sl = new AetherServiceLocator;
        $sl->set('projectRoot', __DIR__.'/templating/');

        $config = new AetherConfig('./aether.config.xml');
        $sl->set('aetherConfig', $config);

        $this->assertInstanceOf(AetherTemplateSmarty::class, $sl->getTemplate());
    }
}
