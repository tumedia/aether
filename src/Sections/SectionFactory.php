<?php

namespace Aether\Sections;

use Aether\ServiceLocator;

class SectionFactory
{
    /**
     * Create an instance of a given section.
     *
     * @param  string $className
     * @param  \Aether\ServiceLocator $sl
     * @return AetherSection
     */
    public static function create($className, ServiceLocator $sl)
    {
        // @todo: can this class be removed?
        return $sl->make($className);
    }
}
