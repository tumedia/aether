<?php

namespace Aether\Sections;

use Aether\Aether;

class SectionFactory
{
    /**
     * Create an instance of a given section.
     *
     * @param  string  $className
     * @param  \Aether\Aether  $aether
     * @return AetherSection
     */
    public static function create($className, Aether $aether)
    {
        // @todo: can this class be removed?
        return $aether->make($className);
    }
}
