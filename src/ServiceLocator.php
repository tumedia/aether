<?php

namespace Aether;

use Exception;
use Aether\Templating\Template;
use Illuminate\Container\Container;
use Illuminate\Container\EntryNotFoundException;

/**
 * Aether service locator, an object to locate services needed
 * Gives access to database, template and other common objects
 *
 * Created: 2007-01-31
 * @author Raymond Julin
 * @package aether
 */
class ServiceLocator extends Container
{
    /**
     * Hold list of vectors.
     *
     * @var array
     */
    public $vectors = [];

    /**
     * Fetch a reference to the templating object.
     *
     * @return \Aether\Templating\Template
     */
    public function getTemplate()
    {
        return $this->make(Template::class);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $name  Name to use as lookup for object
     * @param  object  $object  The actual object
     * @return void
     */
    public function set($name, $object)
    {
        $this->instance($name, $object);
    }

    /**
     * {@inheritdoc}
     *
     * Returns null if the entry is not found.
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (EntryNotFoundException $e) {
            return null;
        }
    }

    /**
     * Resolve a vector object.
     *
     * @param  string  $name
     * @return array
     */
    public function getVector($name)
    {
        return $this->vectors[$name] ?? $this->vectors[$name] = new Vector;
    }

    /**
     * Alias for the "has" method.
     *
     * @depricated  Use `has()` or `bound()` instead.
     */
    public function hasObject($name)
    {
        return $this->has($name);
    }
}
