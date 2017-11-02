<?php

/**
 * This class is a wrapper around Aether Modules that will be rendered when
 * the instance is cast to a string.
 *
 * Before the module is rendered, you may dynamically add options which will
 * be passed to the module instance at render time.
 *
 * @see module()  Use the module() helper function instead of manually
 *                instantiating this class.
 */
class AetherModulePendingRender
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
    protected $options;

    /**
     * Create a new instance.
     *
     * @param  string  $module
     * @param  array  $options
     * @param  string
     */
    public function __construct($module, $options)
    {
        $this->module = $module;
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
        $instance = AetherModuleFactory::create(
            $this->module,
            $this->getServiceLocator(),
            $this->prepareOptions()
        );

        return $instance->run() ?: '';
    }

    /**
     * Get the service locator instance.
     *
     * @return \AetherServiceLocator
     */
    protected function getServiceLocator()
    {
        return Aether::getInstance()->getServiceLocator();
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
        $config = $this->getServiceLocator()->get('config')->get('modules', []);

        return $config[$this->module][$key] ?? [];
    }

    /**
     * Get the Aether config options to merge with the module options.
     *
     * @return array
     */
    protected function getAetherOptionsToMerge()
    {
        if (!$this->getServiceLocator()->has('aetherConfig')) {
            return [];
        }

        $config = $this->getServiceLocator()->get('aetherConfig');

        return $config->getOptions();
    }
}
