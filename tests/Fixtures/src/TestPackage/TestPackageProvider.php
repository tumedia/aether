<?php

namespace Tests\Fixtures\TestPackage;

use Aether\Providers\Provider;

class TestPackageProvider extends Provider
{
    public function register()
    {
        $this->aether->bind('test.package.foo', function () {
            return 'bar';
        });

        $this->fillConfigFrom(__DIR__.'/test-package-config.php', 'test-package');
    }

    public function boot()
    {
        $this->aether->instance('test.package.booted', true);
    }
}
