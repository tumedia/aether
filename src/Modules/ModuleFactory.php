<?php

namespace Aether\Modules;

use Aether\ServiceLocator;

class ModuleFactory
{
    /**
     * Create an instance of a given module.
     *
     * @param  string $className
     * @param  \Aether\ServiceLocator $sl
     * @param  array $options = []
     * @return \AetherModule
     * @throws \InvalidArgumentException
     */
    public static function create(
        $className,
        ServiceLocator $sl,
        $options = []
    ) {
        if (!is_subclass_of($className, AetherModule::class)) {
            throw new InvalidArgumentException("Module [{$className}] does not exist");
        }

        return new $className($sl, $options);
    }
}
