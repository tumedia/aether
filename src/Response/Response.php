<?php

namespace Aether\Response;

/**
 * Define basic aether response object
 *
 * Created: 2007-02-05
 * @author Raymond Julin
 * @package aether.lib
 */
abstract class Response
{
    /**
     * Draw response
     *
     * @access public
     * @return void
     * @param \Aether\ServiceLocator $sl
     */
    abstract public function draw($sl);
    abstract public function get();
}
