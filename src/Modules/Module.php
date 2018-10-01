<?php

namespace Aether\Modules;

use Aether\Aether;

/**
 * Base class definition for aether modules
 *
 * Created: 2007-02-06
 * @author Raymond Julin
 * @package aether.lib
 */
abstract class Module
{
    /**
     * The Aether instance.
     *
     * @var \Aether\Aether
     */
    protected $aether;

    /**
     * Legacy alias for the $aether property.
     *
     * @var \Aether\Aether
     */
    protected $sl;

    /**
     * Specific options for this module.
     *
     * @var array
     */
    protected $options = [];

    /**
     * "Draw" an instance of `PendingRender` for the module.
     * Optionally specifying initial options.
     *
     * @param  string|array|null  $initial  If array, uses as options.
     *                                      If string, calls merge() with the string
     * @return \Aether\Modules\PendingRender
     */
    public static function draw($initial = null)
    {
        $pending = new PendingRender(static::class);

        if (is_string($initial)) {
            $pending->merge(...func_get_args());
        } elseif (is_array($initial)) {
            $pending->setOptions($initial);
        }

        return $pending;
    }

    public function __construct(Aether $aether, $options = [])
    {
        $this->aether = $aether;
        $this->sl = $this->aether;
        $this->options = $options;
    }

    /**
     * Allow each module to decide if caching should be totaly
     * forbidden in a given context. Useful for modules
     * that deliver highly dynamic data based on each request
     * but where it is generic in certain cases
     *
     * Use getCacheTime() instead.
     *
     * @deprecated
     * @access public
     * @return bool
     */
    public function denyCache()
    {
        return false;
    }

    /**
     * Allow each module to decide their cache time (in seconds)
     *
     * @access public
     * @return bool
     */
    public function getCacheTime()
    {
        return $this->denyCache() ? 0 : null;
    }

    /**
     * Get the key used to cache the module output.
     *
     * By default the $key is returned as-is. It is recommended to return a
     * modified version of the $key - for example by appending a state
     * identifier.
     *
     * @param  string  $key
     * @return string
     */
    public function getCacheKey($key)
    {
        return $key;
    }

    /**
     * Render a given service
     *
     * @access public
     * @return \Aether\Response\Response
     */
    public function service($name)
    {
        return null;
    }
}
