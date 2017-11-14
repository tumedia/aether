<?php

class AetherServiceTemplateGlobals extends AetherService
{
    public function register()
    {
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        // Make sure base and root for this request is stored
        // in the service locator so it can be made available
        // to the magical $aether array in templates

        $magic = $this->sl->getVector('templateGlobals');

        $magic['base'] = $config->getBase();
        $magic['root'] = $config->getRoot();
        $magic['urlVars'] = $config->getUrlVars();
        $magic['runningMode'] = $options['AetherRunningMode'];
        $magic['requestUri'] = $_SERVER['REQUEST_URI'];
        $magic['domain'] = $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['HTTP_REFERER'])) {
            $magic['referer'] = $_SERVER['HTTP_REFERER'];
        }

        $magic['options'] = $options;
    }
}
