<?php

namespace Aether\Providers;

use Aether\AetherConfig;

class LocalizationProvider extends Provider
{
    public function boot()
    {
        $options = $this->aether['aetherConfig']->getOptions();

        setlocale(LC_ALL, $options['locale'] ?? 'nb_NO.UTF-8');
        setlocale(LC_NUMERIC, $options['lc_numeric'] ?? 'C');

        if (isset($options['lc_messages'])) {
            setlocale(LC_MESSAGES, $options['lc_messages']);

            bindtextdomain('messages', $this->aether['projectRoot'].'locale');
            bind_textdomain_codeset('messages', 'UTF-8');
            textdomain('messages');
        }
    }
}
