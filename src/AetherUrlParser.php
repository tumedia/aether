<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * Parse an url and make its parts available in an OO way
 * 
 * Created: 2007-02-01
 * @author Raymond Julin
 * @package aether
 */

class AetherUrlParser {
    
    /**
     * Scheme for url
     * @var string
     */
    private $scheme;
    
    /**
     * Host for url
     * @var string
     */
    private $host;
    
    /**
     * Port for url
     * @var int
     */
    private $port;
    
    /**
     * User (if any)
     * @var string
     */
    private $user;
    
    /**
     * Password for user
     * @var string
     */
    private $pass;
    
    /**
     * The path requested
     * @var string
     */
    private $path;
    private $query;
    
    /**
     * Parse an url
     *
     * @access public
     * @return void
     * @param string $url
     */
    public function parse($url) {
        if (!empty($url)) {
            $parts = parse_url($url);
            foreach ($parts as $part => $value) {
                if (property_exists($this, $part))
                    $this->$part = $value;
            }
        }
    }
    
    /**
     * Parse the $_SERVER array directly
     *
     * @access public
     * @return void
     * @param array $server
     */
    public function parseServerArray($server) {
        if (isset($server['HTTPS'])) 
            $this->scheme = 'https';
        else
            $this->scheme = 'http';

        $this->port = $server['SERVER_PORT'];
        $this->host = str_replace(":" . $this->port, '', $server['HTTP_HOST']);
        if (!empty($server['PHP_AUTH_USER']))
            $this->user = $server['PHP_AUTH_USER'];
        if (!empty($server['PHP_AUTH_PW']))
            $this->pass = $server['PHP_AUTH_PW'];

        // Some browsers send the complete url as REQUEST_URI (galaxy tab)
        if (strpos($server['REQUEST_URI'], 'http://') === 0 ||
                strpos($server['REQUEST_URI'], 'https://') === 0) {
            $parts = parse_url($server['REQUEST_URI']);
            $this->path = $parts['path'];
            $this->query = (isset($parts['query']) ? $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }
        else {
            $path = urldecode($server['REQUEST_URI']);
            $qsa = strpos($path, '?');
            if (!$qsa)
                $qsa = strlen($path);
            $this->path = substr($path, 0, $qsa);
            $this->query = substr($path, $qsa + 1);
        }
    }
    
    /**
     * Fetch an url part
     *
     * @access public
     * @return mixed
     * @param string $part
     */
    public function get($part) {
        if (property_exists($this, $part))
            return $this->$part;
        else
            throw new OutOfRangeException("[$part] is not a valid url part");
    }
    
    /**
     * Get parsed url as a basic string
     *
     * @access public
     * @return string
     */
    public function __toString() {
        $url = $this->scheme.'://';
        if (!empty($this->user)) {
            $url .= $this->user;
            if (!empty($this->pass))
                $url .= ':' . $this->pass;
            $url .= '@';
        }
        $url .= $this->host . $this->path;
        return $url;
    }
    
    /**
     * Return url as a system safe string/filename
     *
     * @access public
     * @return string
     */
    public function cacheName() {
        $path = $this->path;
        if (substr($path, -1) != "/")
            $path .= "/";
        return str_replace('/', '_', $this->host . $path);
    }
}
