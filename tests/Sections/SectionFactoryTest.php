<?php

namespace Tests;

use Aether\ServiceLocator;
use Aether\Sections\Section;
use PHPUnit\Framework\TestCase;
use Aether\Sections\SectionFactory;
use Tests\Fixtures\Sections\Testsection;

class SectionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $section = SectionFactory::create(
            Testsection::class,
            new ServiceLocator
        );

        $this->assertInstanceOf(Section::class, $section);
        $this->assertInstanceOf(Testsection::class, $section);
    }
}
