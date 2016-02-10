<?php

/**
 * 
 * Simple hellolocal module, just a test module
 * 
 * Created: 2007-02-06
 * @author Raymond Julin
 * @package aether.module
 */

class Hellolocal extends AetherModule {
    
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        return 'Hello local';
    }
}
