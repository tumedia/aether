<?php

namespace Aether\Services;

use Aether\Templating\Template;
use Aether\Templating\SmartyTemplate;

class TemplateService extends Service
{
    public function register()
    {
        $this->container->singleton(Template::class, function ($container) {
            $template = new SmartyTemplate($container);

            $providers = $container->getVector('aetherProviders');
            $variables = $this->getGlobalVariables($container);

            $template->set('aether', compact('providers') + $variables);

            return $template;
        });
    }

    protected function getGlobalVariables($container)
    {
        $config = $container['aetherConfig'];
        $options = $config->getOptions();

        $variables = [];

        $variables['base'] = $config->getBase();
        $variables['root'] = $config->getRoot();
        $variables['urlVars'] = $config->getUrlVars();
        $variables['runningMode'] = $options['AetherRunningMode'] ?? 'test';

        if ($container->bound('parsedUrl')) {
            $url = $container['parsedUrl'];

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
