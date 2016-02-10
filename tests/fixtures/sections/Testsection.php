<?php // vim:set ts=4 sw=4 et:

require_once(AETHER_PATH . 'lib/AetherSection.php');

/**
 * 
 * Empty shell of a section to use in test case
 * TODO Use mock instead. Requires changes to section factory aswell
 * 
 * Created: 2009-02-17
 * @author Raymond Julin
 * @package aether.test
 */

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
