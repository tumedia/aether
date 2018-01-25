<?php

namespace Aether\Response;

use DOMDocument;

/**
 * XML Response
 *
 * Created: 2007-05-22
 * @author Raymond Julin
 * @package aether.lib
 */
class Xml extends Response
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
        header("Content-Type: text/xml; charset=UTF-8");
        if ($this->struct instanceof DOMDocument) {
            echo $this->struct->saveXML();
        } else {
            echo $this->__toXml($this->struct)->saveXML();
        }
    }

    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get()
    {
        return $this->__toXml($this->struct)->saveXML();
    }

    /**
     * Render to xml
     *
     * @access public
     * @return string
     * @param array $data
     */
    public function __toXml($data, $element = false, $document=false)
    {
        if (!$document) {
            $document = new DOMDocument('1.0', 'UTF-8');
            $element = $document;
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $tmp = $document->createElement($key);

                if (is_array($val)) {
                    $this->__toXml($val, $tmp, $document);
                } else {
                    $tmp->appendChild($document->createTextNode($val));
                }

                $element->appendChild($tmp);
            }
        }
        return $element;
    }
}
