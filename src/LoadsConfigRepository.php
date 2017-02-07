<?php

use Dotenv\Dotenv;
use Illuminate\Config\Repository;
use Dotenv\Exception\InvalidPathException;

trait LoadsConfigRepository
{
    /**
     * Get a config repository instance.
     *
     * @param  string $configPath
     * @return \Illuminate\Config\Repository
     */
    public function getConfigRepository(string $configPath): Repository
    {
        // If a `compiled.php` file exists, we'll use that. Should only be used
        // in a production environment.
        if (file_exists($compiled = $configPath.'/compiled.php')) {
            return new Repository(require $compiled);
        }

        // Otherwise, we'll need to load the configuration files from the
        // `config` folder in our project.

        // First, we need to load the .env file.
        $this->installDotenv(dirname($configPath));

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

        return new Repository($config);
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
