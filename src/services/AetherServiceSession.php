<?php

class AetherServiceSession extends AetherService
{
    public function register()
    {
        session_set_save_handler(
            new AetherSessionHandlerCache($this->getCache())
        );
    }

    protected function getCache()
    {
        return $this->sl->get('cache');
    }
}
