<?php

namespace Aether\Cache;

use Illuminate\Filesystem\Filesystem;

class FileDriver implements Cache
{
    /**
     * Path to the cache storage directory.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new cache instance.
     *
     * @param  string  $storagePath
     * @param  \Illuminate\Fileysystem\Filesystem  $files
     */
    public function __construct($storagePath, Filesystem $files)
    {
        $this->storagePath = $storagePath;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $data, $ttl = INF)
    {
        $path = $this->path($name);

        if (! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true);
        }

        return $this->files->put($path, $this->createPayload($data, $ttl)) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $maxAge = INF)
    {
        $path = $this->path($name);

        if (! $this->files->exists($path)) {
            return false;
        }

        return $this->getFromPayload($this->files->get($path), $maxAge);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->get($name) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function rm($name)
    {
        if ($this->has($name)) {
            $this->files->delete($this->path($name));
        }

        return true;
    }

    /**
     * Get the path to the file for a given cache item.
     *
     * @param  string  $name
     * @return string
     */
    protected function path($name)
    {
        $hash = sha1($name);

        $parts = array_slice(str_split($hash, 2), 0, 2);

        return $this->storagePath.'/'.implode('/', $parts).'/'.$hash;
    }

    /**
     * Unserialize a payload string, validate time-to-live and return the data.
     *
     * @param  string  $string
     * @param  int  $maxAge
     * @return mixed
     */
    protected function getFromPayload($string, $maxAge)
    {
        $payload = unserialize($string);

        $ttl = min($payload['ttl'], $maxAge);

        if ($payload['time'] + $ttl <= time()) {
            return false;
        }

        return $payload['data'];
    }

    /**
     * Create a serialized payload string.
     *
     * @param  mixed  $data
     * @param  int  $ttl
     * @return string
     */
    protected function createPayload($data, $ttl)
    {
        return serialize([
            'time' => time(),
            'ttl'  => $ttl,
            'data' => $data,
        ]);
    }
}
