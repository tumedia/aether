<?php // vim:set ts=4 sw=4 et:
/**
 *
 * Base class definition for aether modules
 *
 * Created: 2007-02-06
 * @author Raymond Julin
 * @package aether.lib
 */

abstract class AetherModule
{
    
    /**
     * Hold service locator
     * @var AetherServiceLocator
     */
    protected $sl = null;
    
    /**
     * Specific options for this module
     * @var array
     */
    protected $options = array();
    
    /**
     * Constructor. Accept service locator
     *
     * @access public
     * @return AetherModule
     * @param AetherServiceLocator $sl
     * @param array $options
     */
    public function __construct(AetherServiceLocator $sl, $options=array())
    {
        $this->sl = $sl;
        $this->options = $options;
    }
    
    /**
     * Run module.
     * Modules is only capable of returning text ouput
     * any http actions must be taken by the section
     *
     * @access public
     * @return string
     */
    abstract public function run();
    
    /**
     * Allow each module to decide if caching should be totaly
     * forbidden in a given context. Useful for modules
     * that deliver highly dynamic data based on each request
     * but where it is generic in certain cases
     *
     * Use getCacheTime() instead.
     *
     * @deprecated
     * @access public
     * @return bool
     */
    public function denyCache()
    {
        return false;
    }
    
    /**
     * Allow each module to decide their cache time (in seconds)
     *
     * @access public
     * @return bool
     */
    public function getCacheTime()
    {
        return $this->denyCache() ? 0 : null;
    }
    
    /**
     * Render a given service
     *
     * @access public
     * @return AetherResponse
     */
    public function service($name)
    {
        return null;
    }
}
