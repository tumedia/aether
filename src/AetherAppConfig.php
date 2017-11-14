<?php

use Dotenv\Dotenv;
use Illuminate\Config\Repository;
use Dotenv\Exception\InvalidPathException;

class AetherAppConfig extends Repository
{
    /**
     * Create a new AetherAppConfig instance. This will automatically load the
     * configuration from the path specified.
     *
     * @param  string $projectRoot  Trailing slash is allowed.
     */
    public function __construct(string $projectRoot)
    {
        parent::__construct($this->loadConfig($projectRoot));
    }

    /**
     * Load the configuration.
     *
     * @param  string $projectRoot
     * @return array  All config items.
     */
    private function loadConfig(string $projectRoot): array
    {
        $projectRoot = rtrim($projectRoot, '/');
        $configPath  = $projectRoot.'/config';

        // If a `compiled.php` file exists, we'll use that. Should only be used
        // in a production environment.
        if (file_exists($compiled = $configPath.'/compiled.php')) {
            return require $compiled;
        }

        // Otherwise, we'll need to load the configuration files from the
        // `config` folder in our project.

        // First, we need to load the .env file.
        $this->installDotenv($projectRoot);

        $config = [];

        foreach (glob($configPath.'/*.php') as $path) {
            @list($configName, $matchEnv) = explode('.', basename($path, '.php'), 2);

            // If the `compiled.php` file is listed, skip it.
            if ($configName === 'compiled') {
                continue;
            }

            if (!isset($config[$configName])) {
                $config[$configName] = require "{$configPath}/{$configName}.php";
            }

            // If the config file is targeting a specific environment (using the
            // "{$config}.{$environment}.php" syntax), we'll use
            // `array_replace_recursive` to merge it into the base config.
            if (
                $matchEnv &&
                $matchEnv === env('APP_ENV') &&
                file_exists($envConfig = "{$configPath}/{$configName}.{$matchEnv}.php")
            ) {
                $config[$configName] = array_replace_recursive(
                    $config[$configName],
                    require $envConfig
                );
            }
        }

        return $config;
    }

    /**
     * Load the environment file using Dotenv.
     *
     * @param  string $path
     * @return void
     */
    private function installDotenv($path)
    {
        try {
            (new Dotenv($path))->load();
        } catch (InvalidPathException $e) {
            // Do nothing if the .env file is not present.
        }
    }
}
