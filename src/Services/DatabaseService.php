<?php

namespace Aether\Services;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseService extends Service
{
    public function register()
    {
        $this->container->singleton('db', function ($container) {
            return new DatabaseManager($container, new ConnectionFactory($container));
        });

        $this->container->booted(function () {
            $this->bootEloquent();
        });
    }

    protected function bootEloquent()
    {
        Eloquent::setConnectionResolver($this->container['db']);

        Eloquent::setEventDispatcher($this->container['events']);
    }
}
