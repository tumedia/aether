<?php

namespace Aether\Modules;

use Exception;
use Throwable;
use Aether\Aether;
use Aether\Config;
use BadMethodCallException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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
        try {
            return $this->runModule();
        } catch (Throwable $e) {
            // If an exception is thrown during render, we'll catch it and send
            // it to the exception handler manually. It needs to be this way
            // because exceptions thrown when casting an object to a string
            // using the `__toString()` method will be caught internally and
            // re-thrown as a "Method must not throw an exception, caught .."
            // error - losing the original exception including information such
            // as the stack trace.

            if (! $e instanceof Exception) {
                $e = new FatalThrowableError($e);
            }

            if (app()->isProduction()) {
                resolve(ExceptionHandler::class)->report($e);
            } else {
                ob_clean();

                resolve(ExceptionHandler::class)->render(null, $e)->draw(app());

                // @todo: figure out a nice way to stop output beyond this point.
            }

            return '';
        }
    }

    /**
     * Instantiate and run the module.
     *
     * @return string
     */
    protected function runModule()
    {
        $factory = resolve(ModuleFactory::class);

        $module = $factory->create($this->module, $this->prepareOptions());

        return $factory->run($module) ?: '';
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
        if (!$this->legacyMode || ! app()->bound('aetherConfig')) {
            return [];
        }

        return app('aetherConfig')->getOptions();
    }
}
