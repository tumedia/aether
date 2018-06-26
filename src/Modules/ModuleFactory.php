<?php

namespace Aether\Modules;

use Aether\Aether;
use InvalidArgumentException;

class ModuleFactory
{
    protected $aether;

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
    }

    /**
     * Create an instance of a given module.
     *
     * @param  string  $className
     * @param  array  $options = []
     * @return \Aether\Modules\Module
     *
     * @throws \InvalidArgumentException
     */
    public function create($className, array $options = [])
    {
        if (! is_subclass_of($className, Module::class)) {
            throw new InvalidArgumentException("Module [{$className}] does not exist");
        }

        return new $className($this->aether, $options);
    }

    /**
     * Run a module and return the output.
     *
     * @param  \Aether\Modules\Module  $module
     * @return string
     */
    public function run(Module $module)
    {
        return $this->aether->call([$module, 'run']);
    }
}
