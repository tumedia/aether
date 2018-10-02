<?php

namespace Aether\Templating;

use Illuminate\Support\Traits\Macroable;

/**
 * Super class for templating interface of Aether
 *
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package aether
 */
abstract class Template
{
    use Macroable;

    /**
     * Set a template variable
     *
     * @return void
     * @param string $key
     * @param mixed $value
     */
    abstract public function set($key, $value);

    abstract public function setAll($keyValues);

    /**
     * Check if a given variable name has been set.
     *
     * @param  string  $key
     * @return bool
     */
    abstract public function hasVariable($key);

    /**
     * Fetch rendered template
     *
     * @return string
     * @param string $name
     */
    abstract public function fetch($name);

    /**
     * Check if template exists
     *
     * @return bool
     * @param string $name
     */
    abstract public function templateExists($name);

    /**
     * Add a path to load templates from.
     *
     * @param  string  $path
     * @return void
     */
    abstract public function addPath($path);

    /**
     * Add a path to load namespaced templates from.
     *
     * @param  string  $path
     * @param  string  $namespace
     * @return void
     */
    abstract public function addNamespace($path, $namespace);

    /**
     * Clear all compiled templates.
     *
     * @return void
     */
    abstract public function clearCompiled();
}
