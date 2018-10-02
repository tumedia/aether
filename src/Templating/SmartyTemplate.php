<?php

namespace Aether\Templating;

use Smarty;
use Aether\ServiceLocator;

/**
 * Facade over Smarty templating engine
 *
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package aether
 */
class SmartyTemplate extends Template
{
    protected $engine;

    public function __construct(ServiceLocator $sl, Smarty $smarty = null)
    {
        $this->engine = $smarty ?: new Smarty;
        $this->sl = $sl;

        $projectRoot = $this->sl->get('projectRoot');

        $this->engine->addTemplateDir($base = "{$projectRoot}/templates");

        $this->engine->error_reporting = E_ALL ^ E_NOTICE;
        $this->engine->plugins_dir = [SMARTY_SYSPLUGINS_DIR, SMARTY_PLUGINS_DIR, $base];
        $this->engine->compile_dir = "{$base}/compiled";
        $this->engine->config_dir = "{$base}/configs";
        $this->engine->cache_dir = "{$base}/cache";

        $options = $this->sl->get('aetherConfig')->getOptions();

        if (isset($options['searchpath'])) {
            $this->addPathsFromOptions($options['searchpath'], $projectRoot);
        }

        // Make sure template files are group writable
        umask(0022);
    }

    /**
     * Set a template variable
     *
     * @return void
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->engine->assign($key, $value);
    }

    public function setAll($keyValues)
    {
        foreach ($keyValues as $key => $value) {
            $this->engine->assign($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariable($key)
    {
        return array_key_exists($key, $this->engine->tpl_vars);
    }

    /**
     * Fetch rendered template
     *
     * @return string
     * @param string $name
     */
    public function fetch($name)
    {
        return $this->engine->fetch($name);
    }

    /**
     * Register plugins to be used in smarty templates
     * Type is smarty's "block" or "function"
     * Name is template tag name
     * Function is callback to be run
     * http://www.smarty.net/docs/en/api.register.plugin.tpl
     *
     * @param string $type
     * @param string $name
     * @param mixed $function
     */
    public function registerPlugin($type, $name, $function)
    {
        $this->engine->registerPlugin($type, $name, $function);
    }

    /**
     * Check if template exists, duh
     *
     * @return bool
     * @param string $name
     */
    public function templateExists($name)
    {
        return $this->engine->templateExists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addPath($path)
    {
        $this->engine->addTemplateDir($path);

        $this->engine->addPluginsDir("{$path}/plugins");
    }

    /**
     * {@inheritdoc}
     */
    public function addNamespace($path, $namespace)
    {
        $this->engine->registerResource(
            $namespace,
            new NamespacedTemplatesSmartyResource($path, $namespace, $this->sl->get('projectRoot'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function clearCompiled()
    {
        $this->engine->clearCompiledTemplate();
    }

    protected function addPathsFromOptions($searchpath, $projectRoot)
    {
        $search = array_map('trim', explode(';', $searchpath));

        foreach ($search as $dir) {
            if (strpos($dir, '.') === 0) {
                $dir = $projectRoot.$dir;
            }

            $this->addPath(rtrim($dir, '/').'/templates');
        }
    }
}
