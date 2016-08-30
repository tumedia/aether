<?php //
/**
 *
 * Super class for templating interface of Aether
 *
 * Created: 2009-04-23
 * @author Raymond Julin
 * @package aether
 */
abstract class AetherTemplate {
    /** @var \AetherServiceLocator */
    protected $sl = null;

    /**
     * Return template object for selected engine
     *
     * @param  string $engine Name of engine to use
     * @param  string AetherServiceLocator $sl
     * @return \AetherTemplate
     */
    public static function get($engine, AetherServiceLocator $sl) {
        if ($engine == 'smarty') {
            $class = 'AetherTemplateSmarty';
        }
        else {
            // Default template engine
            $class = 'AetherTemplateSmarty';
        }
        return new $class($sl);
    }

    /**
     * Set a template variable
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    abstract public function set($key, $value);

    abstract public function setAll($keyValues);

    /**
     * Fetch rendered template
     *
     * @param  string $name
     * @return string
     */
    abstract public function fetch($name);
}
