<?php // vim:set tabstop=4 shiftwidth=4 smarttab expandtab:
/**
 * 
 * Factory for loading modules
 * 
 * Created: 2007-02-06
 * @author Raymond Julin
 * @package aether.lib
 */

class AetherModuleFactory {
    
    /**
     * The path to search for modules in
     * @var string
     */
    static public $path = '';
    
    /**
     * Have a mode strict that says wether or not the factory
     * expects all modules to be under $dir/modules
     * If "false" then $dir will be searched
     * If "true" then $dir/modules (old school style) will be searched (default)
     * @var boolean
     */
    static public $strict = true;
    
    /**
     * Createa instance of module
     *
     * @access public
     * @return AetherModule
     * @param string $module
     * @param AetherServiceLocator $sl
     * @param array $options
     */
    public static function create($moduleName, AetherServiceLocator $sl, $options=array()) {
        if (!strpos(self::$path, ';'))
            $paths = array(self::$path);
        else {
            $paths = array_map('trim', explode(';', self::$path));
        }
        foreach ($paths as $path) {
            if (substr($path, -1) != '/')
                $path .= '/';

            if (self::$strict)
                $path .= 'modules/';

            $file = $path . $moduleName . '.php';

            if (!file_exists($file))
                continue;

            if (substr(realpath($file), 0, strlen($path)) != $path)
                throw new AetherInvalidModuleNameException ("Module name «{$moduleName}» is not a valid module name");

            include_once($file);
            $class = pathinfo($file, PATHINFO_FILENAME);
            $class = ucfirst($class);
            $module = new $class($sl, $options);
            return $module;
        }

        throw new AetherModuleNotFoundException("Module '$moduleName' does not exist in path [" . join(", ", $paths) . "]");
    }
}
