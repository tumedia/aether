<?php

use Aether\Aether;
use Aether\Config;

if (!function_exists('app')) {
    /**
     * Get the available container instance, or resolve an abstract from the
     * container.
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
        return app($name);
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
