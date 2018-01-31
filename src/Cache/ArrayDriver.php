<?php

namespace Aether\Cache;

class ArrayDriver implements Cache
{
    protected $store = [];

    public function set($name, $data, $ttl = INF)
    {
        $this->store[$name] = [
            'time' => time(),
            'ttl'  => $ttl,
            'data' => $data,
        ];

        return true;
    }

    public function get($name, $maxAge = INF)
    {
        if (! array_key_exists($name, $this->store)) {
            return false;
        }

        $payload = $this->store[$name];

        $ttl = min($payload['ttl'], $maxAge);

        if ($payload['time'] + $ttl <= time()) {
            unset($this->store[$name]);

            return false;
        }

        return $this->store[$name]['data'];
    }

    public function has($name)
    {
        return $this->get($name) !== false;
    }

    public function rm($name)
    {
        if ($this->has($name)) {
            unset($this->store[$name]);
        }

        return true;
    }
}
