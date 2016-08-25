<?php // vim:set tabstop=4 shiftwidth=4 smarttab expandtab:
/**
 * 
 * Base definition of a header module
 * Theres quite a bit common code every header type module
 * would want to implement so its done here.
 * Remember to extend this class!
 * 
 * Created: 2007-05-16
 * @author Raymond Julin
 * @package aether.lib
 */

abstract class AetherModuleHeader extends AetherModule {
    /**
     * Apply common variables to the header template
     * Common variables include:
     * * pageStylesheets
     * * pageJavascripts
     * * urlbase
     *
     * @access protected
     * @return void
     * @param Template $tpl
     * @param boolean $options 
     */
    protected function applyCommonVariables($tpl, $options = false) {
        $config = $this->sl->get('aetherConfig');

        if (!$options) 
            $options = $config->getOptions();

        $tpl->set('urlbase', $config->getBase()); 
        // Stylesheets. Read from config and code
        $styles = array();
        if (array_key_exists('styles', $options)) {
            $styles = explode(';', $options['styles']);
        }
        foreach ($this->sl->getVector('styles') as $style) {
            $styles[] = $style;
        }
        $styles = array_map('trim', $styles);
        $styles = array_unique($styles);
        $tpl->set('pageStylesheets', $styles);
        // Scripts
        $scripts = array();
        if (array_key_exists('javascripts', $options)) {
            $scripts = explode(';', $options['javascripts']);
        }
        foreach ($this->sl->getVector('javascripts') as $script) {
            $scripts[] = $script;
        }
        $scripts = array_map('trim', $scripts);
        $scripts = array_unique($scripts);
        $tpl->set('pageJavascripts', $scripts);
    }
}
