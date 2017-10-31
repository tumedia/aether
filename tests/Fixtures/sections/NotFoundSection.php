<?php

namespace Tests\Fixtures\Sections;

use AetherSection;
use AetherTextResponse;

class NotFoundSection extends AetherSection
{
    public $options;

    /**
     * Return response
     *
     * @return AetherResponse
     */
    public function response()
    {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        $response = new AetherTextResponse('404 Eg fant han ikkje', 'text/html');

        // Nasty hack to send options to test
        $response->options = $options;

        return $response;
    }
}
