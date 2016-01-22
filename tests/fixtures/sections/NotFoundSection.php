<?php

class NotFoundSection extends AetherSection {

    public $options;

    /**
     * Return response
     *
     * @access public
     * @return AetherResponse
     */
    public function response() {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        $response = new AetherTextResponse('404 Eg fant han ikkje', 'text/html');

        // Nasty hack to send options to test
        $response->options = $options;

        return $response;
    }
}
