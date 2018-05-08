<?php

namespace Aether\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Aether\Providers\Provider;
use Symfony\Component\Console\Command\Command;

class AetherCliProvider extends Provider
{
    public function register()
    {
        $this->commands([
            Commands\ConfigClearCommand::class,
            Commands\ConfigGenerateCommand::class,
            Commands\TemplatesClearCommand::class,
            Commands\TinkerCommand::class,
        ]);

        if ($directory = $this->getAppCommandsDirectory()) {
            $this->loadAppCommands($directory);
        }
    }

    /**
     * Get the directory that application console commands live within.
     * Returns null if the directory doesn't exist.
     *
     * @return string|null
     */
    protected function getAppCommandsDirectory()
    {
        $configuredPath = config('app.commands_path', '/src/Commands');

        $directory = realpath($this->aether['projectRoot']).$configuredPath;

        return is_dir($directory) ? $directory : null;
    }

    /**
     * Automatically load commands defined within the application.
     *
     * @param  string  $directory
     * @return void
     */
    protected function loadAppCommands($directory)
    {
        $namespace = $this->aether->getNamespace();
        $srcPath = realpath($this->aether['projectRoot']).'/src';

        foreach (glob($directory.'/*.php') as $file) {
            $command = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file, $srcPath.'/')
            );

            if ($this->shouldLoad($command)) {
                $this->commands([$command]);
            }
        }
    }

    /**
     * Determine if a given command should be automatically loaded.
     *
     * @param  string  $command
     * @return bool
     */
    protected function shouldLoad($command)
    {
        return is_subclass_of($command, Command::class) &&
            ! (new ReflectionClass($command))->isAbstract();
    }
}
