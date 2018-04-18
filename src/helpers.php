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
        $config = resolve('config');

        if (is_null($key)) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

if (! function_exists('template')) {
    /**
     * Get a fresh template engine instance, or render a template.
     *
     * If $name is set, the given template will be rendered automatically. If
     * "$data" is set, the "setAll()" method will be called before render.
     *
     * @param  string  $name = null
     * @param  array  $data = null
     * @return \Aether\Templating\Template|string
     */
    function template($name = null, array $data = null)
    {
        $instance = resolve('template');

        if (is_null($name)) {
            return $instance;
        }

        if (is_array($data)) {
            $instance->setAll($data);
        }

        return $instance->fetch($name);
    }
}
