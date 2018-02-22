<?php

namespace Aether;

use Dotenv\Dotenv;
use Illuminate\Config\Repository;
use Dotenv\Exception\InvalidPathException;

class Config extends Repository
{
    /**
     * Boolean flag to check if the configuration values were loaded from the
     * pre-compiled file.
     *
     * @var bool
     */
    protected $loadedFromCompiled = false;

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
     * Determine if the configuration was loaded from the compiled file.
     *
     * @return bool
     */
    public function wasLoadedFromCompiled()
    {
        return $this->loadedFromCompiled;
    }

    /**
     * Save the entire configuration array to a file.
     *
     * @param  string  $file
     * @return void
     */
    public function saveToFile($file)
    {
        $data = '<?php return '.var_export($this->all(), true).';';

        file_put_contents($file, $data);
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
            $this->loadedFromCompiled = true;

            return require $compiled;
        }

        // Otherwise, we'll need to load the configuration files from the
        // `config` folder in our project.

        // First, we need to load the .env file.
        $this->installDotenv($projectRoot);

        $config = [];

        foreach (glob($configPath.'/*.php') as $path) {
            @list($configName, $matchEnv) = explode('.', basename($path, '.php'), 2);

            // If the config file is *not* targeting an environment, go ahead
            // and load it.
            if (! $matchEnv) {
                $config[$configName] = require "{$configPath}/{$configName}.php";
            }
            // Otherwise, we'll check if the target environment matches the
            // actual environment before merging it in.
            elseif ($matchEnv === env('APP_ENV')) {
                $config[$configName] = array_replace_recursive(
                    $config[$configName] ?? [],
                    require "{$configPath}/{$configName}.{$matchEnv}.php"
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
