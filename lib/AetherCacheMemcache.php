<?php 

/**
 * Handle caching
 * Usage examples
 * <code>
 * // Caching of an object/array/string/whatever datatype
 * if ($obj = $cache->get('foobar')) {
 * }
 * else {
 *     $obj = Foo::getBar();
 *     $cache->set('foobar', $obj, 3600); // Save for 1 hour
 * }
 * </code>
 *
 * Created: 2010-05-03
 * @author Raymond Julin <raymond.julin@gmail.com>
 * @package Aether
 */
 
class AetherCacheMemcache implements AetherCacheInterface { 

    /**
     * Connection
     * @array Memcache
     */
    private $con = null;

    /**
     * Setup memcache servers as supplied
     *
     * @access public
     * @return bool
     * @param string $serversString (list of memcache hosts <host1>:<port>;<host2>:<port>;...)
     */
    public function __construct($serversString="") {
        if ($serversString != "") {
            $this->con = new Memcache;
            $tmp = explode(";", $serversString);
            foreach ($tmp as $s) {
                $hostInfo = explode(":",$s);
                // Assume default port
                if (count($hostInfo) == 1)
                    $hostInfo[] = 11211;

                $this->con->addServer($hostInfo[0], $hostInfo[1], true, 1);
            }
        }
        else {
            // Fall back to file cache or whatbnot ?
        }
    }
    
    /**
     * Save some object
     *
     * @param string $name Key
     * @param mixed $data The data to save
     * @param int $ttl Time to live
     * @return bool
     */
    public function set($name, $data, $ttl=false) {
        if (!is_numeric($ttl)) 
            $ttl = 0;

        $toSave['ttl'] = $ttl;
        $toSave['date'] = time();
        $toSave['data'] = serialize($data);

        return $this->con->set($name, $toSave, 0, $ttl);
    }


    /**
     * Fetch something from cache
     *
     * @access public
     * @return mixed
     * @param string $name Name of object to save as
     * @param int $maxAge
     */
    public function get($name, $maxAge = false) {
        //Get data from cache
        $cache = $this->con->get($name);

        if ($cache === false)
            return false;

        $ttl = ($maxAge === false) ? $cache['ttl'] : $maxAge;
        
        // Check that stored data hasn't expired
        if ($ttl == 0 || ($cache['date'] + $ttl > time())) {
            // We want the cache no matter how old it is
            return unserialize($cache['data']);
        }
        else if (isset($cache['updateDate'])) {
            // The cache has expired but someone else is probably generating a
            // new one. We need to check the updateDate if it should time out 
            // the attempt or just return old cache while waiting for the 
            // generating process to finish.

            if ((time() - $cache['updateDate']) > $this->updateTimeout) {
                $cache['updateDate'] = time();
                $this->con->set($name, $cache, 0, 0);
                return false;
            }
            else
                return unserialize($cache['data']);
        }
        else {
            // The cache doesn't exist so we'll assume someone will create it
            // after we return false.  We'll save the time so that this
            // attempt at creating a cache can time out.

            $cache['updateDate'] = time();
            $this->con->set($name, $cache, 0, 0);
            return false;
        }

    }

    /*
     * Deletes the entry belonging to $name
     *
     * @param string $name
     * @return bool
     */
    public function rm($name) {
        return $this->con->delete($name);
    }

    
    /**
     * Check if an object has been cached
     *
     * @access public
     * @return bool 
     * @param string $name
     */
    public function has($name) {
        if ($this->get($name, false) != false)
            return true;
        else 
            return false;
    }
} 
