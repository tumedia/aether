<?php // vim:set tabstop=4 shiftwidth=4 smarttab expandtab:

/**
 * 
 * Issue a http redirect.
 * By doing this as an exception it'll bubble up from a low level to a
 * a level that have sufficient information to know how to react
 * 
 * Created: 2007-04-30
 * @author Raymond Julin
 */

class AetherRedirectException extends Exception {
    private $url;
    
    /**
     * Constructor
     * Take url as param
     *
     * @access public
     * @param string $url
     */
    public function redirect($url) {
        $this->url = $url;
    }
    
    /**
     * Perform redirection
     *
     * @access public
     * @return void
     */
    public function redirect() {
        header("Location: {$this->url}");
    }
    
    /**
     * Get message
     *
     * @access public
     * @return string
     */
    public function getMessage() {
        $msg = 'Redirection exception, not meant to be printed.';
        $msg .= " Supposed to redirect to [{$this->url}].";
        return $msg;
    }
    
    /**
     * String representation
     *
     * @access public
     * @return string
     */
    public function __toString() {
        return $this->url;
    }
}
