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
        return $_ENV[$key] ?? $default;
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
