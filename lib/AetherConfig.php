<?php // vim:set ts=4 sw=4 et:

require_once("AetherExceptions.php");

/**
 * 
 * Read in config file for aether and make its options
 * available for the system
 * 
 * Created: 2007-02-01
 * @author Raymond Julin
 * @package aether
 */

class AetherConfig {
    
    /**
     * XMLDoc
     * @var DOMDocument
     */
    private $doc;
    
    /**
     * Default rule
     * @var Object
     */
    private $defaultRule = false;
    
    /**
     * What section was found
     * @var string
     */
    private $section;
    
    /**
     * How long should this section be cached
     * @var int/false
     */
    private $cache = false;
    private $cacheas = false;
    
    /**
     * What control template should be used for layout
     * @var string
     */
    private $template;
    
    /**
     * What modules should be included
     * @var array
     */
    private $modules = array();
    
    /**
     * Option settings for this section (highly optional)
     * @var array
     */
    private $options = array();
    
    /**
     * Whats left of the request path when url parsing is finished
     * @var array
     */
    private $path;
    
    /**
     * Variables found in the url
     * @var arra
     */
    private $urlVariables = array();
    
    /**
     * If set, a specific base to be used for all urls within app
     * @var string
     */
    private $urlBase = '/';
    private $urlRoot = '/';
    
    /**
     * When matching parts of the url, should the path fragment
     * maintain its first slash or not.
     * @var string "keep" or "skip"
     */
    private $slashMode = "skip";
    
    /**
     * Hold config file path
     * @var string
     */
    private $configFilePath;

    private $matchedNodes = array();
    
    /**
     * Constructor.
     *
     * @access public
     * @return AetherConfig
     * @param string $configFilePath
     */
    public function __construct($configFilePath) {
        $this->configFilePath = $configFilePath;
    }

    /**
     * Match an url against this config
     *
     * @access public
     * @return bool
     * @param AetherUrlParser $url
     */
    public function matchUrl(AetherUrlParser $url) {
        $configFilePath = $this->configFilePath;
        if (!file_exists($configFilePath)) {
            throw new AetherMissingFileException(
                "Config file [$configFilePath] is missing.");
        }
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->load($configFilePath);
        $this->doc = $doc;
        $xpath = new DOMXPath($doc);


        /*
         * Find starting point of url rules, from this point on we
         * use recursion to decide on the correct rule to use
         */
        $sitename = $url->get('host');
        $xquery = "/config/site[@name='$sitename']";
        $xquery .= '/urlRules';
        $nodelist = $xpath->query($xquery);
        // Fallback to the "any" site qualifier
        if ($nodelist->length == 0) {
            $sitename = '*';
            $xquery = "/config/site[@name='$sitename']/urlRules";
            $nodelist = $xpath->query($xquery);
        }
        if ($nodelist->length == 0) {
            throw new AetherNoUrlRuleMatchException("No config entry matched site: $sitename");
        }
        $urlRules = $nodelist->item(0);

        // Subtract global options
        $ruleBase = " | /config/site[@name='$sitename']/urlRules/";
        $xquery = "/config/site[@name='$sitename']/option";
        $xquery .= $ruleBase . 'section';
        $xquery .= $ruleBase . 'template';
        $xquery .= $ruleBase . 'module';
        $xquery .= $ruleBase . 'option';
        $optionList = $xpath->query($xquery);
        if ($optionList->length > 0)
            $this->readNodeConfiguration($optionList);
        $path = $url->get('path');
        $explodedPath = explode('/', substr($path,1));
        
        // Treat /foo/bar the same as /foo/bar/
        if (end($explodedPath) !== "")
            $explodedPath[] = "";

        /**
         * If AetherSlashMode is "keep", make sure $current is prefixed
         * with a slash as the slash is not maintained from earlier
         */
        if ($this->slashMode() == 'keep') {
            foreach ($explodedPath as $key => $part) {
                $explodedPath[$key] = '/' . $part;
            }
        }
        try {
            $node = $this->findMatchingConfigNode($urlRules, $explodedPath);
        }
        catch (AetherNoUrlRuleMatchException $e) {
            // No match found :( Send 404 and throw exception to logs
            header("Status: 404 Not Found");
            echo "<html><body><h1>404 Not found</h1></body></html>";

            throw new Exception("Technical error. No resource found on this url: " . (string)$url . ", " . $e);
        }
        catch (Exception $e) {
            // This is expected
            // Comment above was exceptionally not expected -- simeng 2011-10-10
        }
    }
    
    private function containsRules($node) {
        foreach ($node->childNodes as $c) {
            if ($c->nodeName === 'rule')
                return true;
        }
        return false;
    }

    private function findRecursive($nodeList, $path) {
        $current = array_shift($path);

        foreach ($nodeList as $node) {
            // This have to be a DOMElement
            if ($node instanceof DOMElement && $node->nodeName == 'rule') {
                if ($this->matches($current, $node)) {
                    $this->matchedNodes[] = $node;
                    /**
                     * If this node is a match, and has child nodes
                     * then try to crawl the next level aswell, see
                     * if a more exact match is possible
                     */
                    if ($this->containsRules($node)) 
                        return $this->findRecursive($node->childNodes, $path);
                    else
                        return $node;
                }
            }
        }

        return false;
    }

    /**
     * Find matching config node from nodelist and path
     *
     * @access private
     * @return node
     * @param DOMNodeList $list
     * @param array $path the/path/sliced into an array(the,path,sliced).
     * if AetherSlashMode is "keep", then "/fragment" will be used, 
     * else "fragment" will be used when matching nodes.
     */
    private function findMatchingConfigNode($urlRules, $path) {
        // Crawl the config hierarchy till the right node is found

        $this->path = $path;

        // First node is urlRules xml tag
        $this->matchedNodes[] = $urlRules;

        $match = $this->findRecursive($urlRules->childNodes, $path);

        /**
         * No rules matched so far, look for a default rule in the matched
         * nodes starting at the deepest match
         */
        if (!$match) {
            while ($n = array_pop($this->matchedNodes)) {
                foreach ($n->childNodes as $cn) {
                    if ($cn->nodeName == 'rule' && $cn->getAttribute("default")) {
                        $match = $cn;
                    }
                }
            }
        }

        if ($match) {
            $n = $match;
            do {
                if ($n->nodeName == 'rule') {
                    $this->readNodeConfiguration($n);
                }
            }
            while (($n = $n->parentNode) && $n->nodeName != "#document");

            return true;
        }

        throw new AetherNoUrlRuleMatchException("\"" . $_SERVER['REQUEST_URI'] . "\" does not match any rule, and no default rule was found");
    }
    
    /**
     * Check if an url fragment matches the match or pattern
     * attribute for an url rule.
     * There are two ways to apply url rule matching:
     * Match: A simple string checking if the url part exactly
     * matches the match attribute. Very good for defining sections
     * like "video" or "articles"
     * Pattern: A full fledged PCRE match. Suited when you need to
     * assure the matching part only consists of numbers, or that
     * it doesnt contain special signs, or need to be a minimum length
     * When using pattern matching you need to type a valid regex, 
     * making it harder to use: pattern="/[0-9]+/"
     *
     * @access private
     * @return bool
     * @param string $check
     * @param object $node
     */
    private function matches($check, $node) {
        $matches = false;
        if ($node->hasAttribute('match')) {
            if ($node->getAttribute('match') == $check || 
                    ($node->getAttribute('match') === '' && $check === null)) {
                $matches = true;
            }
            else {
                $matches = false;
            }
            $store = $check;
        }
        elseif ($node->hasAttribute('pattern') && $check !== '') {
            $matches = preg_match(
                $node->getAttribute('pattern'), $check, $captures);
            /**
             * When using pattern based matching make sure we store
             * the last matching part of the array of regex matches
             */
            if (is_array($captures))
                $store = array_pop($captures);
        }
        if ($matches) {
            // Store value of url fragment, typical stores and id
            if ($node->hasAttribute('store') AND isset($store)) {
                $this->storeVariable(
                    $node->getAttribute('store'), $store);
            }
            // Remember the url base if this is it
            if ($node->hasAttribute('isBase'))
                $this->urlBase .= $check.'/';
            if ($node->hasAttribute('isRoot'))
                $this->urlRoot .= $check.'/';
            return true;
        }
        return false;
    }
    
    /**
     * Given a nodelist, read section, subsection and other data
     * from that node and store it in self
     *
     * @access private
     * @return void
     * @param DOMNode $node
     */
     private function readNodeConfiguration($node) {
        if ($node instanceof DOMNode) {
            if ($node->hasAttribute('cache'))
                $this->cache = $node->getAttribute('cache');
            if ($node->hasAttribute('cacheas'))
                $this->cacheas = $node->getAttribute('cacheas');
            $nodelist = $node->childNodes;
        }
        else {
            $nodelist = $node;
        }
        foreach ($nodelist as $child) {
            if ($child instanceof DOMText)
                continue;
            switch ($child->nodeName) {
                case 'section': 
                    $this->section = $child->nodeValue;
                    break;

                case 'template':
                    if (!isset($this->template)) {
                        $tpl = array();
                        $tpl['name'] = $child->nodeValue;
                        $this->template = $tpl;
                    }
                    break;

                case 'module':
                    // Modules can contain options, which we need to take into account
                    $text = '';
                    $opts = array();
                    foreach ($child->childNodes as $option) {
                        if ($option->nodeName == '#text')
                            $text .= $option->nodeValue;
                        if ($option->nodeName == 'option')
                            $opts[$option->getAttribute('name')] = $option->nodeValue;
                    }
                        
                    // Merge options from all scopes together
                    $options = array_merge($this->options, $opts);
                    $module = array();
                    //$module = array(
                    $module['name'] = trim($text);
                    $module['options'] = $options;
                    $module['_'] = null;

                    if ($child->hasAttribute('cache'))
                        $module['cache'] = $child->getAttribute('cache');
                    if ($child->hasAttribute('cacheas'))
                        $module['cacheas'] = $child->getAttribute('cacheas');

                    /**
                     * A module could provide itself under a fake name
                     * For example AmobilHeader could be provided as
                     * "header", this object would then be usable
                     * elsewhere in the application
                     */
                    if ($child->hasAttribute('provides'))
                        $module['provides'] = trim($child->getAttribute('provides'));

                    $this->modules[] = $module;
                    break;

                case 'option':
                    $name = $child->getAttribute('name');
                    // Support additive options
                    $mode = "overwrite";
                    if ($child->hasAttribute("mode")) {
                        if (array_key_exists($name, $this->options)) {
                            $mode = $child->getAttribute("mode");
                            $prev = array_map(
                                "trim", explode(";", $this->options[$name]));
                            $opts = array_map(
                                "trim", explode(";", $child->nodeValue));
                        }
                    }
                    switch ($mode) {
                        case 'add':
                            /**
                             * If mode is "add", add to ; separated list
                             * and ensure no duplicates are created?
                             */
                            // Add everything that doesnt create dupes
                            foreach ($opts as $opt) {
                                if (!in_array($opt, $prev))
                                    $prev[] = $opt;
                            }
                            $value = implode(";", $prev);
                            break;
                        case 'del':
                             // If mode is "del", delete from ; list
                            $value = implode(";", array_diff($prev, $opts));
                            break;
                        default:
                            // Simple string/int value
                            $value = trim($child->nodeValue);
                            break;
                    }
                    $this->options[$name] = $value;
                    break;
            }
        }
    }
    
    /**
     * Store a variable fetched from the url
     *
     * @access private
     * @return void
     * @param string $key
     * @param mixed $val
     */
    public function storeVariable($key, $val) {
        $this->urlVariables[$key] = $val;
    }
    
    /**
     * Get section
     *
     * @access public
     * @return string
     */
    public function getSection() {
        return $this->section;
    }
    
    /**
     * Get cache time
     *
     * @access public
     * @return int/bool
     */
    public function getCacheTime() {
        return $this->cache;
    }
    public function getCacheName() {
        return $this->cacheas;
    }
    
    /**
     * Get requested control templates name
     *
     * @access public
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }
    
    /**
     * Get array over what modules should be used when rendering page
     *
     * @access public
     * @return array
     */
    public function getModules() {
        // Reorder modules so providers are first
        $regular = array();
        $providers = array();
        foreach ($this->modules as $module) {
            if (array_key_exists('provides', $module))
                $providers[] = $module;
            else
                $regular[] = $module;
            
        }
        return array_merge($providers, $regular);
    }
    
    /**
     * Set modules that should be used when rendering page
     *
     * @access public
     * @return void
     */
    public function setModules($modules) {
        $this->modules = $modules;    
    }
    
    /**
     * Get all options set for section
     *
     * @access public
     * @return array
     * @param array $defaults Provide a set of defaults to use if no value is set
     */
    public function getOptions($defaults=array()) {
        return array_merge($defaults, $this->options);
    }

    /**
     * Set and/or change an option
     *
     * Ex: use it to change or add a config option from within a section
     *
     * @access public
     */
    
    public function setOption($name, $value) {
        $this->options[$name] = $value;
    }

    /**
     * Get an array of all urlVariables set.
     * These are the variables in a regex url ex. /ads/([0-9]+)/images
     * which are stored with the store="name"-attribute
     */
    public function getUrlVars() {
        return $this->urlVariables;
    }
    
    /**
     * Get an url variable.
     * These are the variables in a regex url ex. /ads/([0-9]+)/images
     * which are stored with the store="name"-attribute
     */
    public function getUrlVar($key) {
        if ($this->hasUrlVar($key)) 
            return $this->urlVariables[$key];
        else
            throw new Exception("[$key] is not an existing variable");
    }
    
    /**
     * Check if url var exists
     *
     * @return bool
     * @param string $key
     */
    public function hasUrlVar($key) {
        return array_key_exists($key, $this->urlVariables);
    }

    /**
     * Get an url variable (DEPRECATED: use getUrlVar())
     *
     * @access public
     * @return mixed
     * @param string $key
     */
    public function getUrlVariable($key) {
        return $this->getUrlVar($key);
    }
    
    /**
     * Fetch url base
     *
     * @access public
     * @return string
     */
    public function getBase() {
        return $this->urlBase;
    }
    public function getRoot() {
        return $this->urlRoot;
    }
    
    /**
     * Fetch whats left and "unusued" of the path originaly requested
     *
     * @access public
     * @return array
     */
    public function getPathLeftOvers() {
        return $this->path;
    }
    
    /**
     * What slashmode are Aether running in
     *
     * @access public
     * @return string
     */
    public function slashMode() {
        $opts = $this->getOptions();
        if (isset($opts['AetherSlashMode'])) 
            $this->slashMode = $opts['AetherSlashMode'];

        return $this->slashMode;
    }
    
    /**
     * Get configuration file path
     *
     * @access public
     * @return string
     */
    public function configFilePath() {
        return $this->configFilePath;
    }
}
