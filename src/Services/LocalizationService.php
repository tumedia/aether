<?php

namespace Aether\Services;

use Aether\AetherConfig;

class LocalizationService extends Service
{
    public function register()
    {
        $this->container->booted(function ($container) {
            $options = $container['aetherConfig']->getOptions();

            setlocale(LC_ALL, $options['locale'] ?? 'nb_NO.UTF-8');
            setlocale(LC_NUMERIC, $options['lc_numeric'] ?? 'C');

            if (isset($options['lc_messages'])) {
                setlocale(LC_MESSAGES, $options['lc_messages']);

                bindtextdomain('messages', $container['projectRoot'].'locale');
                bind_textdomain_codeset('messages', 'UTF-8');
                textdomain('messages');
            }
        });
    }
}
