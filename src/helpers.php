<?php

use Aether\Aether;
use Aether\Config;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed|\Aether\Aether
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Aether::getInstance();
        }

        return Aether::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @param  string  $name
     * @return mixed
     */
    function resolve($name)
    {
        return app($abstract);
    }
}

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
        $config = app('config');

        if (is_null($key)) {
            return $config;
        }

        return $config->get($key, $default);
    }
}
