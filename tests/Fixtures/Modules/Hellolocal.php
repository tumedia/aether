<?php

namespace Tests\Fixtures\Modules;

use AetherModule;

class Hellolocal extends AetherModule
{
    /**
     * Render module
     *
     * @return string
     */
    public function run()
    {
        return 'Hello local';
    }
}
