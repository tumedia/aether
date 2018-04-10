<?php

namespace Aether\Console;

use Illuminate\Console\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    /**
     * @var \Aether\Aether
     */
    protected $aether;

    /**
     * {@inheritdoc}
     */
    public function setLaravel($laravel)
    {
        parent::setLaravel($laravel);

        $this->aether = $laravel;
    }

    public function getAether()
    {
        return $this->aether;
    }
}
