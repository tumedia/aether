
<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * Fragment response
 * 
 * Created: 2014-06-05
 * @author Simen Graaten
 * @package aether.lib
 */

class AetherFragmentResponse extends AetherResponse {
    
    /**
     * Modules in this fragment
     */
    private $modules;
    
    
    public function __construct($moduleResponses) {
        $this->moduleResponses = $moduleResponses;
    }
    
    /**
     * Draw text response. Echoes out the response
     *
     * @access public
     * @return void
     * @param AetherServiceLocator $sl
     */
    public function draw($sl) {
        header("Content-Type: application/json");
        echo json_encode($this->get());
    }
    
    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get() {
        foreach ($this->moduleResponses as $id => $resp) 
            $out[$id] = $resp->get();
        return $out;
    }
}
