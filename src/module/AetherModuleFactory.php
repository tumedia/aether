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
     */
    public static function create(
        $className,
        AetherServiceLocator $sl,
        $options = []
    ) {
        return new $className($sl, $options);
    }
}
