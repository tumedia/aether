<?php // vim:set tabstop=4 shiftwidth=4 smarttab expandtab:

class AetherModuleFactory
{
    /**
     * Create an instance of a given module.
     *
     * @param  string $className
     * @param  AetherServiceLocator $sl
     * @param  array $options = []
     * @return \AetherModule
     * @throws \InvalidArgumentException
     */
    public static function create(
        $className,
        AetherServiceLocator $sl,
        $options = []
    ) {
        if (!is_subclass_of($className, AetherModule::class)) {
            throw new InvalidArgumentException("Module [{$className}] does not exist");
        }

        return new $className($sl, $options);
    }
}
