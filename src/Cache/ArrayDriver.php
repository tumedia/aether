<?php

namespace Aether\Cache;

class ArrayDriver implements Cache
{
    protected $store = [];

    public function set($name, $data, $ttl = false)
    {
        $this->store[$name] = [
            'time' => time(),
            'ttl'  => $ttl,
            'data' => $data,
        ];
    }

    public function get($name, $maxAge = false)
    {
        return $this->has($name) ? $this->store[$name] : false;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->store);
    }

    public function rm($name)
    {
        if ($this->has($name)) {
            unset($this->store[$name]);
        }

        return true;
    }
}
