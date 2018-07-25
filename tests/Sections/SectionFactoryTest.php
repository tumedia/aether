<?php

namespace Tests\Sections;

use Tests\TestCase;
use Aether\Sections\Section;
use Aether\Sections\SectionFactory;
use Tests\Fixtures\Sections\GenericTestSection;

class SectionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $section = SectionFactory::create(
            GenericTestSection::class,
            $this->aether
        );

        $this->assertInstanceOf(Section::class, $section);
        $this->assertInstanceOf(GenericTestSection::class, $section);
    }
}
