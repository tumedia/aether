<?php

namespace Aether\Response;

/**
 * Fragment response
 *
 * Created: 2014-06-05
 * @author Simen Graaten
 * @package aether.lib
 */

class Fragment extends Response
{
    /**
     * Modules in this fragment
     */
    private $modules;

    public function __construct($moduleResponses)
    {
        $this->moduleResponses = $moduleResponses;
    }

    /**
     * Draw text response. Echoes out the response
     *
     * @access public
     * @return void
     * @param \Aether\ServiceLocator $sl
     */
    public function draw($sl)
    {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($this->get());
    }

    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get()
    {
        $out = [];
        foreach ($this->moduleResponses as $id => $resp) {
            if ($resp) {
                $out[$id] = $resp->get();
            }
        }
        return $out;
    }
}
