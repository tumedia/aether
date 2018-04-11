<?php

namespace Tests\Fixtures\Modules;

use AetherModule;

class OptionsSerializer extends AetherModule
{
    public function run()
    {
        return serialize($this->options);
    }
}
