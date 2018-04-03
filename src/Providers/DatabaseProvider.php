<?php

namespace Aether\Providers;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('db', function ($aether) {
            return new DatabaseManager($aether, new ConnectionFactory($aether));
        });
    }

    public function boot()
    {
        Eloquent::setConnectionResolver($this->aether['db']);

        Eloquent::setEventDispatcher($this->aether['events']);
    }
}
