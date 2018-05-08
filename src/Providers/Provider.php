<?php

namespace Aether\Providers;

use Aether\Aether;
use Aether\Config;
use Aether\Console\AetherCli;

abstract class Provider
{
    /**
     * The Aether application instance.
     *
     * @var \Aether\Aether
     */
    protected $aether;

    public function __construct(Aether $aether)
    {
        $this->aether = $aether;
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Load base configuration from a given file. If the local app config
     * already contains data under the given key, this method will not
     * overwrite any existing values, it will simply fill in the blanks.
     * Perfect for a package to load its base config.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function fillConfigFrom($path, $key)
    {
        $config = $this->aether['config'];

        if ($config->wasLoadedFromCompiled()) {
            return;
        }

        $localConfig = $config->get($key, []);

        $config->set($key, array_replace_recursive(require $path, $localConfig));
    }

    /**
     * Register Aether CLI commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function commands(array $commands)
    {
        AetherCli::starting(function ($aetherCli) use ($commands) {
            $aetherCli->resolveCommands($commands);
        });
    }

    /**
     * Add a path to load templates from, optionally under a given namespace.
     *
     * @param  string  $path
     * @param  string|null  $namespace
     * @return void
     */
    protected function loadTemplatesFrom($path, $namespace = null)
    {
        $this->aether->resolving('template', function ($template) use ($path, $namespace) {
            if (is_null($namespace)) {
                $template->addPath($path);
            } else {
                $template->addNamespace($path, $namespace);
            }
        });
    }
}
