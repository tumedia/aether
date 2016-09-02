<?php // vim:set ts=4 sw=4 et:

/**
 *
 * Aether service locator, an object to locate services needed
 * Gives access to database, template and other common objects
 *
 * Created: 2007-01-31
 * @author Raymond Julin
 * @package aether
 */

class AetherServiceLocator {
    /**
     * Hold custom objects
     * @var array
     */
    private $custom = array();

    /**
     * Hold list of vectors
     * @var array
     */
    public $vectors = array();

    /**
     * Get the Aether View Factory instance.
     *
     * @param  string|null $view
     * @param  array       $data = []
     * @return \AetherView
     */
    public function view($view = null, array $data = [])
    {
        if (!isset($this->custom['view'])) {
            new AetherViewInstaller($this);
        }

        if (!is_null($view)) {
            return $this->custom['view']->make($view, $data);
        }

        return $this->custom['view'];
    }

    /**
     * Fetch a reference to the templating object
     * thats floating around in Aether
     *
     * @access public
     * @return AetherTemplate A template object
     *
     * @depricated  Use `AetherServiceLocator::view`
     */
    public function getTemplate()
    {
        return $this->view();
    }

    /**
     * Returns a reference to a database object
     *
     * @access public
     * @return Database Requested database object
     * @param string $name database name
     */
    public function getDatabase($name) {
        throw new Exception("AetherServiceLocator::getDatabase() is deprecated");
    }

    /**
     * Save a custom object to the service locators storage
     * This functionality is meant for sharing objects between
     * components (Subsection and FooComponent)
     * Only one unique object per name can be held
     *
     * @access public
     * @return void
     * @param string $name Name to use as lookup for object
     * @param object $object The actual object
     */
    public function set($name, $object) {
        $this->custom[$name] = $object;
    }

    /**
     * Fetch a custom object
     *
     * @access public
     * @return object
     * @param string $name
     */
    public function get($name) {
        if ($this->has($name))
            return $this->custom[$name];
        else
            throw new Exception('Custom object ['.$name.'] does not exist');
    }

    /**
     * Give access to vector x
     *
     * @access public
     * @return array
     * @param string $name
     */
    public function getVector($name) {
        if (!isset($this->vectors[$name]))
            $this->vectors[$name] = new AetherVector;
        return $this->vectors[$name];
    }

    public function hasObject($name) {
        return $this->has($name);
    }
    public function has($name) {
        return array_key_exists($name, $this->custom);
    }

    /**
     * Get the Bundle Manager instance or a Bundle instance.
     *
     * @param  string|null  $name
     * @return \AetherBundleManager|\AetherBundle
     */
    public function bundle(string $name = null)
    {
        if (!isset($this->custom['bundle_manager'])) {
            $this->custom['bundle_manager'] = new AetherBundleManager($this);
        }

        if (!is_null($name)) {
            return $this->custom['bundle_manager']->get($name);
        }

        return $this->custom['bundle_manager'];
    }
}
