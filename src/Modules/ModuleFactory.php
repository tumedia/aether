<?php

namespace Aether\Modules;

use Aether\ServiceLocator;
use InvalidArgumentException;

class ModuleFactory
{
    /**
     * Create an instance of a given module.
     *
     * @param  string $className
     * @param  \Aether\ServiceLocator $sl
     * @param  array $options = []
     * @throws \InvalidArgumentException
     * @return \Aether\Modules\Module
     */
    public static function create(
        $className,
        ServiceLocator $sl,
        $options = []
    ) {
        if (!is_subclass_of($className, Module::class)) {
            throw new InvalidArgumentException("Module [{$className}] does not exist");
        }

        return new $className($sl, $options);
    }
}
