<?php

namespace Aether\Cache;

interface Cache
{
    public function set($name, $data, $ttl = false);

    public function get($name, $maxAge = false);

    public function has($name);

    public function rm($name);
}
