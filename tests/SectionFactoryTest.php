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

        $this->assertInstanceOf(AetherSection::class, $section);
        $this->assertInstanceOf(Testsection::class, $section);
    }
}
