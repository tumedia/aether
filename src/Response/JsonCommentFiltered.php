<?php

namespace Aether\Response;

/**
 * JSON response
 *
 * Created: 2008-10-23
 * @author Raymond Julin
 * @package aether.lib
 */
class JsonCommentFiltered extends Response
{
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

    /**
     * Constructor
     *
     * @access public
     * @param array $structure
     */
    public function __construct($structure)
    {
        $this->struct = $structure;
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
        echo $this->get();
    }

    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get()
    {
        return "/* " . json_encode($this->struct) . " */";
    }
}
