<?php

namespace Tests\Fixtures\Sections;

use AetherSection;
use AetherTextResponse;

class Testsection extends AetherSection
{
    /**
     * Return response
     *
     * @return AetherResponse
     */
    public function response()
    {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        if (!empty($options['id']) &&
            $options['id'] == 'invalid') {
            return $this->triggerDefaultRule();
        }

        return new AetherTextResponse($this->renderModules());
    }
}
