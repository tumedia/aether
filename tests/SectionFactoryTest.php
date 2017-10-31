<?php

namespace Tests;

use AetherSection;
use AetherSectionFactory;
use AetherServiceLocator;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Sections\Testsection;

class SectionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $section = AetherSectionFactory::create(
            Testsection::class,
            new AetherServiceLocator
        );

        $this->assertTrue(is_subclass_of($section, AetherSection::class));
        $this->assertEquals(get_class($section), Testsection::class);
    }
}
