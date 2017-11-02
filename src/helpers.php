<?php

if (!function_exists('env')) {
    /**
     * Get an environment variable.
     *
     * @param  string $key
     * @param  mixed  $default = null
     * @return mixed  Returns the value of `$default` if the environment
     *                variable is not set.
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        $valueLength = strlen($value);

        if ($valueLength > 1 && strpos($value, '"') === 0 && strrpos($value, '"') === $valueLength - 1) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get the config repository instance, or if `$key` is set, return the
     * corresponding config value.
     *
     *
     * @param  string $key = null                   Optional
     * @param  mixed  $default = null
     * @return \Illuminate\Config\Repository|mixed
     */
    function config(string $key = null, $default = null)
    {
        $config = Aether::getInstance()->getServiceLocator()->get('config');

        if (is_null($key)) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('module')) {
    /**
     * Get an "AetherModulePendingRender" instance for a given module. Useful
     * as a shorthand for use in templates.
     *
     * @param  string  $module
     * @param  array  $options
     * @return \AetherModulePendingRender
     */
    function module($module, $options = [])
    {
        return new AetherModulePendingRender($module, $options);
    }
}
