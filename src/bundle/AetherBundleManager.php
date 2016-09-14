<?php

class AetherBundleManager
{
    /** @var \AetherServiceLocator */
    protected $sl;

    /** @var array */
    protected $bundles = [];

    /**
     * Constructor.
     *
     * @param  \AetherServiceLocator  $sl
     */
    public function __construct(AetherServiceLocator $sl)
    {
        $this->sl = $sl;
    }

    /**
     * Get a registered bundle by its name (class name.)
     *
     * @param  string  $name
     * @return \AetherBundle
     */
    public function get(string $name): AetherBundle
    {
        return $this->bundles[$name];
    }

    /**
     * Load and bootstrap bundle instances.
     *
     * @return void
     */
    public function bootstrap()
    {
        $bundlesFile = $this->sl->get('projectRoot').'/config/bundles.php';

        if (!file_exists($bundlesFile)) {
            return;
        }

        foreach (require $bundlesFile as $bundle) {
            $instance = new $bundle($this->sl);

            if ($instance->bootstrap() !== false) {
                $this->bundles[$bundle] = $instance;
            }
        }
    }
}
