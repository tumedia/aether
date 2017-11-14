<?php

class AetherSessionHandlerCache implements SessionHandlerInterface
{
    protected $cache;

    /**
     * Create a new session handler instance.
     *
     * @param  \AetherCache  $cache
     */
    public function __construct(AetherCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($id)
    {
        return $this->cache->get($this->getCacheName($id)) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        $this->cache->set($this->getCacheName($id), $data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        $this->cache->rm($this->getCacheName($id));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Get the cache name for a given session id.
     *
     * @param  string  $id
     * @return string
     */
    protected function getCacheName($id)
    {
        return 'session-'.$id;
    }
}
