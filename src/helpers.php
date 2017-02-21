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
    function env(string $key, $default = null) {
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
    function config(string $key = null, $default = null) {
        return Aether::getInstance()->getServiceLocator()->get('config')->get($key, $default);
    }
}
