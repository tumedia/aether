<?php // 
/**
 *
 * Facade over Smarty templating engine
 *
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package aether
 */

class AetherTemplateSmarty extends AetherTemplate {
    /**
     * Construct
     *
     * @param AetherServiceLocator $sl
     */
    public function __construct(AetherServiceLocator $sl) {
        $this->engine = new Smarty;
        $this->sl = $sl;
        $options = $this->sl->get('aetherConfig')->getOptions();

        $root = $this->sl->get('projectRoot');
        $base = $root . 'templates/';
        // Add project root first in template search path
        $templateDirs[] = $base;
        $pluginDirs = array(SMARTY_SYSPLUGINS_DIR, SMARTY_PLUGINS_DIR, $base);
        if (isset($options['searchpath'])) {
            $search = array_map("trim", explode(";", $options['searchpath']));
            foreach ($search as $dir) {
                if (strpos($dir, ".") === 0) 
                    $dir = $root . $dir;
                $templateDirs[] = $dir . "templates/";
                $pluginDirs[] = $dir . "templates/plugins/";
            }
        }
        $this->engine->error_reporting = E_ALL ^ E_NOTICE;
        $this->engine->template_dir = $templateDirs;
        $this->engine->plugins_dir = $pluginDirs;
        $this->engine->compile_dir = $base . 'compiled/';
        $this->engine->config_dir = $base . 'configs/';
        $this->engine->cache_dir = $base . 'cache/';

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
    public function set($key,$value) {
        $this->engine->assign($key, $value);
    }

    public function setAll($keyValues) {
        foreach ($keyValues as $key => $value) {
            $this->engine->assign($key, $value);
        }
    }

    /**
     * Fetch rendered template
     *
     * @return string
     * @param string $name
     */
    public function fetch($name) {
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
    public function registerPlugin($type, $name, $function) {
        $this->engine->registerPlugin($type, $name, $function);
    }
}
