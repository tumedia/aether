<?php

class NotFoundSection extends AetherSection {

    /**
     * Return response
     *
     * @access public
     * @return AetherResponse
     */
    public function response() {
        return new AetherTextResponse('404 Eg fant han ikkje', 'text/html');
    }
}
