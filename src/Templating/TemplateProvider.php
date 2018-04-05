<?php

namespace Aether\Templating;

use Aether\Providers\Provider;

class TemplateProvider extends Provider
{
    public function register()
    {
        $this->aether->bind('template', function ($aether) {
            $template = new SmartyTemplate($aether);

            $providers = $aether->getVector('aetherProviders');
            $variables = $this->getGlobalVariables($aether);

            $template->set('aether', compact('providers') + $variables);

            return $template;
        });

        $this->aether->singleton('template.global', function ($aether) {
            return $aether['template'];
        });
    }

    protected function getGlobalVariables($aether)
    {
        $config = $aether['aetherConfig'];
        $options = $config->getOptions();

        $variables = [];

        $variables['base'] = $config->getBase();
        $variables['root'] = $config->getRoot();
        $variables['urlVars'] = $config->getUrlVars();
        $variables['runningMode'] = $options['AetherRunningMode'] ?? 'test';

        if ($aether->bound('parsedUrl')) {
            $url = $aether['parsedUrl'];

            $variables['requestUri'] = $this->getRequestUri($url);
            $variables['domain'] = $url->get('host');
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $variables['referer'] = $_SERVER['HTTP_REFERER'];
        }

        $variables['options'] = $options;

        return $variables;
    }

    protected function getRequestUri($url)
    {
        return $url->get('path').($url->get('query') ? '?'.$url->get('query') : '');
    }
}
