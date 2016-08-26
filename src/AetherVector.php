<?php // vim:set tabstop=4 shiftwidth=4 smarttab expandtab:

/**
 * 
 * Arrays reimplemented!
 * Ridiculous maybe, but we need something that will be passed as
 * reference, something that acts more or less like an array but is not
 * Basic usage:
 *<code>
 * $vector = new AetherVector('foo', 'bar');
 * echo $vector[0]; // 'foo'
 * $vector[] = 'test';
 * // Alternative syntax
 * $vector->append('foo');
 * $vector->contains('foo'); // true
 *</code>
 *
 * Besides, we can get some syntax sugar like:
 *<code>
 * $arr = new AetherVector('foo');
 * $arr2 = new AetherVector('bar');
 * $arr->join($arr2);
 *</code>
 * 
 * Created: 2007-04-25
 * @author Raymond Julin
 * @package commonlibs
 */

class AetherVector implements ArrayAccess,Iterator {
    private $data = array();
    private $valid = true;
    
    /**
     * Construct object
     *
     * @access public
     */
    public function __construct() {
        $args = func_get_args();
        foreach ($args as $key => $arg) {
            $this->data[$key] = $arg;
        }
    }
    
    /**
     * Test if value is contained
     *
     * @access public
     * @return bool
     * @param mixed $object
     */
    public function contains($object) {
        return in_array($object, $this->data);
    }
    public function append($element) {
        $this->data[] = $element;
        return $this;
    }
    
    
    /**
     * Methods needed for array access
     *
     * @access public
     * @return mixed
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->data);
    }
    public function offsetGet($offset) {
        return $this->data[$offset]; 
    }
    public function offsetSet($offset, $value) {
        if (empty($offset))
            $offset = count($this->data);
        $this->data[$offset] = $value;
    }
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    public function getAsArray() {
        return $this->data;
    }
    
    /**
     * Methods needed for Iterator
     *
     * @access public
     * @return mixed
     */
    public function current() {
        return current($this->data);
    }
    public function key() {
        return key($this->data);
    }
    public function next() {
        $this->valid = (next($this->data) !== false); 
    }
    public function rewind() {
        $this->valid = (reset($this->data) !== false); 
    }
    public function valid() {
        return $this->valid;
    }
}
