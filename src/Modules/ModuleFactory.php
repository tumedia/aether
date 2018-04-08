<?php

namespace Aether\Modules;

use Aether\Aether;
use InvalidArgumentException;

class ModuleFactory
{
    /**
     * Create an instance of a given module.
     *
     * @param  string  $className
     * @param  \Aether\Aether  $aether
     * @param  array  $options = []
     * @return \Aether\Modules\Module
     * @throws \InvalidArgumentException
     */
    public static function create(
        $className,
        Aether $aether,
        $options = []
    ) {
        if (!is_subclass_of($className, Module::class)) {
            throw new InvalidArgumentException("Module [{$className}] does not exist");
        }

        return new $className($aether, $options);
    }
}
