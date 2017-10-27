<?php // vim:set ts=4 sw=4 et:
/**
 *
 * Define basic aether response object
 *
 * Created: 2007-02-05
 * @author Raymond Julin
 * @package aether.lib
 */

abstract class AetherResponse
{
    
    /**
     * Draw response
     *
     * @access public
     * @return void
     * @param AetherServiceLocator $sl
     */
    abstract public function draw($sl);
    abstract public function get();
}
