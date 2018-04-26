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
}
