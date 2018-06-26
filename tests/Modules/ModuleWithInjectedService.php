<?php

namespace Tests\Modules;

use Aether\Modules\Module;

class ModuleWithInjectedService extends Module
{
    public function run(FooService $foo)
    {
        return $foo->getFoo();
    }
}

class FooService
{
    public function getFoo()
    {
        return 'foo';
    }
}
