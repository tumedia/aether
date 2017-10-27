<?php // vim:set ts=4 sw=4 et:

class AetherSectionFactory
{
    /**
     * Create an instance of a given section.
     *
     * @param  string $className
     * @param  AetherServiceLocator $sl
     * @return AetherSection
     */
    public static function create($className, AetherServiceLocator $sl)
    {
        return new $className($sl);
    }
}
