<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * Factory for creating instances of section objects
 * Usage:
 * <code>
 * $section = AetherSectionFactory::create('Priceguide');
 * </code>
 * 
 * Created: 2007-02-02
 * @author Raymond Julin
 * @package aether.lib
 */

class AetherSectionFactory {
    
    /**
     * The path to search for sections in
     * @var 
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
     * Create an instance of a section/subsection combination
     *
     * @access public
     * @return AetherSection
     * @param string $section
     * @param AetherServiceLocator $sl
     */
    public static function create($section, AetherServiceLocator $sl) {
        if (!empty($section)) {
            if (!strpos(self::$path, ';'))
                $paths = array(self::$path);
            else {
                $paths = array_map('trim', explode(';', self::$path));
            }
            foreach ($paths as $path) {
                if (substr($path, -1) != '/')
                    $path .= '/';

                if (self::$strict)
                    $file = $path . 'sections/' . $section . '.php';
                else
                    $file = $path . $section . '.php';

                if (file_exists($file)) {
                    require_once($file);
                    $class = pathinfo($file, PATHINFO_FILENAME);
                    $class = ucfirst($class);
                    $aetherSection = new $class($sl);
                    return $aetherSection;
                }
            }
            $pathString = implode(';', $paths);
            throw new Exception("Failed to locate section [$section] in searchpath: $pathString");
        }
        else {
            throw new Exception('AetherSectionFactory::create() received an 
                empty $section variable therefore a section could not be found. 
                This probably means that the matched rule in the configuration 
                file didn\'t contain a <section/>-tag');
        }
    }
}
