<?php

namespace Aether\Cache;

/**
 * Dummy class with no caching implemented
 */
class Cache
{
    public function set($name, $data, $ttl=false)
    {
        return false;
    }
    public function get($name, $maxAge = false)
    {
        return false;
    }
    public function has($name)
    {
        return false;
    }
    public function rm($name)
    {
        return false;
    }
}
