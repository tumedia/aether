<?php // vim:set ts=4 sw=4 et:

class Testsection extends AetherSection {

    /**
     * Return response
     *
     * @access public
     * @return AetherResponse
     */
    public function response() {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        if (!empty($options['id']) &&
            $options['id'] == 'invalid')
        {
            return $this->triggerDefaultRule();
        }

        return new AetherTextResponse($this->renderModules());
    }
}
