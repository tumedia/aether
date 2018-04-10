<?php

namespace Tests\Templating;

use Tests\TestCase;
use Aether\Templating\SmartyTemplate;

class TemplateTest extends TestCase
{
    public function testGettingATemplateObjectThroughAether()
    {
        $this->setUrl('/');

        $this->assertInstanceOf(
            SmartyTemplate::class,
            $this->aether->getTemplate()
        );
    }
}
