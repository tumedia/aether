<?php

namespace Aether\Modules;

use Aether\Aether;
use Aether\Config;
use Aether\AetherConfig;
use BadMethodCallException;

/**
 * This class is a wrapper around Aether Modules that will be rendered when
 * the instance is cast to a string.
 *
 * Before the module is rendered, you may dynamically add options which will
 * be passed to the module instance at render time.
 */
class PendingRender
{
    /**
     * Class name of the module to render.
     *
     * @var string
     */
    protected $module;

    /**
     * The options that will be passed to the module instance.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Whether or not options from the Aether XML config should be used.
     *
     * @var bool
     */
    protected $legacyMode = false;

    /**
     * Create a new instance.
     *
     * @param  string  $module
     * @param  string
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Set the options property.
     *
     * @param  array  $options
     * @return void
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Merge pre-defined options from the config into the module options.
     *
     * @param  string  ...$keys
     * @return \AetherModulePendingRender  $this
     */
    public function merge(...$keys)
    {
        foreach ($keys as $key) {
            $this->options = array_replace_recursive(
                $this->options,
                $this->getConfigOptionsToMerge($key)
            );
        }

        return $this;
    }

    /**
     * Whether or not options from the Aether XML config should be used.
     *
     * @param  bool  $value = true
     * @return \AetherModulePendingRender  $this
     */
    public function legacyMode($value = true)
    {
        $this->legacyMode = $value;

        return $this;
    }

    /**
     * Allow dynamically calling methods beginning with the keyword "with" to
     * set any option.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return \AetherModulePendingRender  $this
     * @throws \BadMethodCallException
     */
    public function __call($method, $arguments)
    {
        if (strpos($method, 'with') !== 0) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        $key = lcfirst(substr($method, 4));

        $this->options[$key] = $arguments[0];

        return $this;
    }

    /**
     * Run the module and return the output.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->runModule();
    }

    /**
     * Instantiate and run the module.
     *
     * @return string
     */
    protected function runModule()
    {
        $instance = ModuleFactory::create(
            $this->module,
            Aether::getInstance(),
            $this->prepareOptions()
        );

        return $instance->run() ?: '';
    }

    /**
     * Prepare the final options which will be passed to the module instance.
     *
     * @return array
     */
    protected function prepareOptions()
    {
        return array_replace_recursive(
            $this->getAetherOptionsToMerge(),
            $this->options
        );
    }

    /**
     * Get the options to merge for a given key. Options are retrieved from
     * "config/modules.php" and they are grouped by the module class name.
     *
     * @param  string  $key
     * @return array
     */
    protected function getConfigOptionsToMerge($key)
    {
        $config = config('modules', []);

        return $config[$this->module][$key] ?? [];
    }

    /**
     * Get the Aether config options to merge with the module options.
     *
     * @return array
     */
    protected function getAetherOptionsToMerge()
    {
        if (!$this->legacyMode || !Aether::getInstance()->bound(AetherConfig::class)) {
            return [];
        }

        $config = Aether::getInstance()->make(AetherConfig::class);

        return $config->getOptions();
    }
}
