<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * JSON response
 * 
 * Created: 2009-03-13
 * @author Mads Erik Forberg
 * @package aether.lib
 */

class AetherJSONPResponse extends AetherResponse {
    
    /**
     * Hold text string for output
     * @var string
     */
    private $struct;
    
    /**
     * Hold cached output
     * @var string
     */
    private $out = '';

    private $callback;
    
    /**
     * Constructor
     *
     * @access public
     * @return AetherJSONResponse
     * @param array $structure
     */
    public function __construct($structure, $callback) {
        $this->struct = $structure;
        $this->callback = $callback;
    }
    
    /**
     * Draw text response. Echoes out the response
     *
     * @access public
     * @return void
     * @param AetherServiceLocator $sl
     */
    public function draw($sl) {
        echo $this->get();
    }
    
    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get() {
        header("Content-Type: application/javascript; charset=UTF-8");
        return $this->callback . "(" . json_encode($this->struct) .")";
    }
}
