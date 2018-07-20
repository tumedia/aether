<?php

namespace Aether;

class Localization
{
    protected $lc_all;
    protected $lc_numeric;
    protected $lc_messages;
    protected $lc_time;
    protected $path;
    protected $localized = false;

    public function __construct($options, $localizationPath) {
        $this->lc_all = $options['locale'] ?? 'nb_NO.UTF-8';
        $this->lc_numeric = $options['lc_numeric'] ?? 'C';
        $this->lc_messages = $options['lc_messages'] ?? null;
        $this->lc_time = $options['lc_time'] ?? null;
        $this->path = $localizationPath;

        setlocale(LC_ALL, $this->lc_all);
        setlocale(LC_NUMERIC, $this->lc_numeric);

        $this->setLocalization($this->lc_messages);
    }

    public function setLocalization($locale) {
        if (!isset($locale))
            return false;

        $this->localized = true;
        $this->lc_messages = $locale;
        $this->lc_time = $locale;

        setlocale(LC_MESSAGES, $this->lc_messages);
        setlocale(LC_TIME, $this->lc_time);
        bindtextdomain('messages', $this->path);
        textdomain('messages');
    }

    public function getLocalization() {
        return $this->lc_messages ?? false;
    }
}
