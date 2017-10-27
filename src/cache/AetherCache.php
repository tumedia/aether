<?php

/**
 * Dummy class with no caching implemented
 */
class AetherCache
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
