<?php

namespace Aether;

use DOMNode;
use DOMText;
use DOMXPath;
use Exception;
use DOMElement;
use DOMDocument;
use Aether\Exceptions\MissingFile;
use Aether\Exceptions\NoUrlRuleMatch;
use Illuminate\Filesystem\Filesystem;

/**
 * Read in config file for aether and make its options
 * available for the system
 *
 * Created: 2007-02-01
 * @author Raymond Julin
 * @package aether
 */
class AetherConfig
{
    /**
     * XMLDoc
     * @var \DOMDocument
     */
    private $doc;

    /**
     * What section was found
     * @var string
     */
    private $section;

    /**
     * How long should this section be cached
     * @var int|false
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
    private $modules = [];

    /**
     * Option settings for this section (highly optional)
     * @var array
     */
    private $options = [];

    /**
     * Variables found in the url
     * @var arra
     */
    private $urlVariables = [];

    /**
     * If set, a specific base to be used for all urls within app
     * @var string
     */
    private $urlBase = '/';
    private $urlRoot = '/';

    /**
     * Hold config file path
     * @var string
     */
    private $configFilePath;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    private $matchedNodes = [];

    /**
     * Constructor.
     *
     * @param  string  $configFilePath
     * @param  \Illuminate\Filesystem\Filesystem  $files
     */
    public function __construct($configFilePath, Filesystem $files = null)
    {
        $this->configFilePath = $configFilePath;
        $this->files = $files ?: new Filesystem;
    }

    /**
     * Save the entire XML document to a file.
     *
     * @param  string  $file
     * @return void
     */
    public function saveToFile($file)
    {
        $this->loadDocFromFile($this->configFilePath)->save($file);
    }

    private function resolveImportNode($doc, $importNode)
    {
        $path = $this->getFileToImport($importNode->nodeValue);
        $parent = $importNode->parentNode;

        $importedDoc = $this->loadDocFromFile($path)->documentElement;

        foreach ($importedDoc->childNodes as $node) {
            $parent->insertBefore($doc->importNode($node, true), $importNode);
        }

        $parent->removeChild($importNode);
    }

    private function getFileToImport($name)
    {
        $directory = dirname($this->configFilePath).'/'.dirname($name);
        $fileName = basename($name, '.xml').'.xml';

        if (! app()->isProduction() && $this->files->exists($path = $directory.'/test.'.$fileName)) {
            return $path;
        }

        if ($this->files->exists($path = $directory.'/'.$fileName)) {
            return $path;
        }

        if ($this->files->exists($path = $directory.'/prod.'.$fileName)) {
            return $path;
        }

        throw new MissingFile("Config named [{$name}] is missing");
    }

    private function loadDocFromFile($file)
    {
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;

        if (app()->isProduction()) {
            $doc->loadXML($this->files->get($file));
        } else {
            $doc->loadXML(
                preg_replace('/cache="[0-9]*"/', '', $this->files->get($file))
            );
        }

        $importNodes = (new DOMXPath($doc))->query('//import');

        foreach ($importNodes as $importNode) {
            $this->resolveImportNode($doc, $importNode);
        }

        $this->injectLegacyAetherRunningMode($doc);

        return $doc;
    }

    private function injectLegacyAetherRunningMode($doc)
    {
        $siteNodes = (new DOMXPath($doc))->query('/config/site');

        $prefix = app()->isProduction() ? 'prod' : 'test';

        foreach ($siteNodes as $siteNode) {
            $option = $doc->createElement('option', $prefix);
            $option->setAttribute('name', 'AetherRunningMode');
            $siteNode->insertBefore($option, $siteNode->firstChild);
        }
    }

    private function getSiteConfig($url)
    {
        $this->doc = $this->loadDocFromFile($this->configFilePath);

        $xpath = new DOMXPath($this->doc);

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
            throw new NoUrlRuleMatch("No config entry matched site: $sitename");
        }
        $urlRules = $nodelist->item(0);

        $path = $url->get('path');
        $explodedPath = explode('/', substr($path, 1));

        // Treat /foo/bar the same as /foo/bar/
        if (end($explodedPath) !== "") {
            $explodedPath[] = "";
        }

        return [
            'rules' => $urlRules,
            'path' => $explodedPath
        ];
    }

    /**
     * Match an url against this config
     *
     * @access public
     * @return bool
     * @param \Aether\UrlParser $url
     */
    public function matchUrl(UrlParser $url)
    {
        $config = $this->getSiteConfig($url);
        try {
            $node = $this->readMatchingConfigNode($config['rules'], $config['path']);
        } catch (NoUrlRuleMatch $e) {
            // No match found :( Send 404 and throw exception to logs
            header("Status: 404 Not Found");
            echo "<html><body><h1>404 Not found</h1></body></html>";

            throw new Exception("Technical error. No resource found on this url: " . (string)$url . ", " . $e);
        } catch (Exception $e) {
            // This is expected
            // Comment above was exceptionally not expected -- simeng 2011-10-10
        }
    }

    private function containsRules($node)
    {
        foreach ($node->childNodes as $c) {
            if ($c->nodeName === 'rule') {
                return true;
            }
        }
        return false;
    }

    private function findRecursive($nodeList, $path)
    {
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
                    $matchingChild = false;
                    if ($this->containsRules($node)) {
                        $matchingChild = $this->findRecursive($node->childNodes, $path);
                    }

                    /**
                     * If this node has no matches and this is
                     * the last path part, return current node.
                     */
                    if ($matchingChild === false &&
                        count($path) <= 1) {
                        return $node;
                    }

                    return $matchingChild;
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
    private function findMatchingConfigNode($urlRules, $path)
    {
        $match = $this->findRecursive($urlRules->childNodes, $path);

        /**
         * No complete rule matched so far, look for a default rule in the matched
         * nodes starting at the deepest match
         */
        if (!$match) {
            $match = $this->getDefaultRule();
        }

        return $match;
    }

    /**
     * Finds the deepest matching default rule
     */
    private function getDefaultRule()
    {
        if (!empty($this->matchedNodes)) {
            while ($node = array_pop($this->matchedNodes)) {
                foreach ($node->childNodes as $childNode) {
                    if ($childNode->nodeName == 'rule' &&
                        $childNode->getAttribute("default")) {
                        return $childNode;
                    }
                }
            }
        }

        throw new Exception('Missing default rule');
    }

    private function loadConfigFromConfigNode($node)
    {
        // Fetch the complete path of nodes back to document
        $n = $node;
        $readNodes = [];
        do {
            if ($n->nodeName == 'rule' ||
                $n->nodeName == 'urlRules' ||
                $n->nodeName == 'site') {
                $readNodes[] = $n;
            }
        } while (($n = $n->parentNode) && $n->nodeName != "#document");

        // Read in the config for each node
        while ($n = array_pop($readNodes)) {
            $nodeConfig = $this->getNodeConfig($n);
            $this->readNodeConfig($nodeConfig);
        }

        return true;
    }

    private function readMatchingConfigNode($urlRules, $path)
    {
        // Crawl the config hierarchy till the right node is found
        // First node is urlRules xml tag
        $this->matchedNodes[] = $urlRules;

        $match = $this->findMatchingConfigNode($urlRules, $path);

        if ($match) {
            return $this->loadConfigFromConfigNode($match);
        } else {
            throw new NoUrlRuleMatch("\"{$_SERVER['REQUEST_URI']}\" does not match any rule, and no default rule was found");
        }
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
    private function matches($check, $node)
    {
        $matches = false;
        if ($node->hasAttribute('match')) {
            if ($node->getAttribute('match') == $check ||
                    ($node->getAttribute('match') === '' && $check === null)) {
                $matches = true;
            } else {
                $matches = false;
            }
            $store = $check;
        } elseif ($node->hasAttribute('pattern') && $check !== '') {
            $matches = preg_match(
                $node->getAttribute('pattern'),
                $check,
                $captures
            );
            /**
             * When using pattern based matching make sure we store
             * the last matching part of the array of regex matches
             */
            if (is_array($captures)) {
                $store = array_pop($captures);
            }
        }
        if ($matches) {
            // Store value of url fragment, typical stores and id
            if ($node->hasAttribute('store') and isset($store)) {
                $this->storeVariable(
                    $node->getAttribute('store'),
                    $store
                );
            }
            // Remember the url base if this is it
            if ($node->hasAttribute('isBase')) {
                $this->urlBase .= $check.'/';
            }
            if ($node->hasAttribute('isRoot')) {
                $this->urlRoot .= $check.'/';
            }
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
    private function readNodeConfig($nodeConfig)
    {
        if (isset($nodeConfig['cache'])) {
            $this->cache = $nodeConfig['cache'];
        }
        if (isset($nodeConfig['cacheas'])) {
            $this->cacheas = $nodeConfig['cacheas'];
        }
        if (isset($nodeConfig['section'])) {
            $this->section = $nodeConfig['section'];
        }
        if (isset($nodeConfig['template'])) {
            $this->template = $nodeConfig['template'];
        }

        if (isset($nodeConfig['modules'])) {
            $count = count($this->modules);
            foreach ($nodeConfig['modules'] as &$nc) {
                $nc['num'] = $count++;
            }

            $this->modules = $nodeConfig['modules'] + ($this->modules ? $this->modules : []);
        }
        if (isset($nodeConfig['options'])) {
            $this->options = $nodeConfig['options'] + ($this->options ? $this->options : []);
        }
        if (isset($nodeConfig['optionDel'])) {
            foreach ($nodeConfig['optionDel'] as $k => $v) {
                if (isset($this->options[$k])) {
                    $this->options[$k] = join(';', array_filter(explode(';', $this->options[$k]), function ($x) use ($v) {
                        return $x !== $v;
                    }));
                }
            }
        }
        if (isset($nodeConfig['optionAdd'])) {
            foreach ($nodeConfig['optionAdd'] as $k => $v) {
                if (isset($this->options[$k])) {
                    $this->options[$k] .= ";" . $v;
                } else {
                    $this->options[$k] = $v;
                }
            }
        }
    }

    /**
     * Fetch node config for a specific node
     */
    private function getNodeConfig($node)
    {
        $nodeData = [];
        if ($node instanceof DOMNode) {
            if ($node->hasAttribute('cache')) {
                $nodeData['cache'] = $node->getAttribute('cache');
            }
            if ($node->hasAttribute('cacheas')) {
                $nodeData['cacheas'] = $node->getAttribute('cacheas');
            }
            $nodelist = $node->childNodes;
        } else {
            $nodelist = $node;
        }

        foreach ($nodelist as $child) {
            if ($child instanceof DOMText) {
                if (empty($nodeData['text'])) {
                    $nodeData['text'] = '';
                }

                $nodeData['text'] .= $child->nodeValue;
            }

            switch ($child->nodeName) {
                case 'section':
                    $nodeData['section'] = $child->nodeValue;
                    break;

                case 'template':
                    $nodeData['template'] = [
                        'name' => $child->nodeValue
                    ];
                    break;

                case 'module':
                    // Merge options from all scopes together
                    $module = [
                        'name' => null,
                        'options' => [],
                        'output' => null
                    ];

                    $moduleConfig = $this->getNodeConfig($child);

                    if ($child->hasAttribute('priority')) {
                        $module['priority'] = $child->getAttribute('priority');
                    }
                    if (isset($moduleConfig['text'])) {
                        $module['name'] = trim($moduleConfig['text']);
                    }
                    if (isset($moduleConfig['cache'])) {
                        $module['cache'] = $moduleConfig['cache'];
                    }
                    if (isset($moduleConfig['cacheas'])) {
                        $module['cacheas'] = $moduleConfig['cacheas'];
                    }
                    if (isset($moduleConfig['options'])) {
                        $module['options'] = $moduleConfig['options'];
                    }


                    if ($child->hasAttribute('provides')) {
                        $module['provides'] = trim($child->getAttribute('provides'));
                    }

                    $nodeId = isset($module['provides']) ? $module['provides'] : $module['name'];

                    $nodeData['modules'][$nodeId] = $module;
                    break;

                case 'option':
                    $name = $child->getAttribute('name');
                    // Support additive options
                    $mode = "overwrite";
                    if ($child->hasAttribute("mode")) {
                        $mode = $child->getAttribute("mode");
                    }
                    $value = trim($child->nodeValue);

                    if (
                        $child->hasAttribute("type") &&
                        $child->getAttribute("type") === "bool"
                    ) {
                        $value = filter_var($child->nodeValue, FILTER_VALIDATE_BOOLEAN);
                    }

                    switch ($mode) {
                        case 'add':
                            $nodeData['optionAdd'][$name] = $value;
                            break;
                        case 'del':
                            $nodeData['optionDel'][$name] = $value;
                            break;
                        default:
                            $nodeData['options'][$name] = $value;
                            break;
                    }
                    break;
            }
        }

        return $nodeData;
    }

    /**
     * Store a variable fetched from the url
     *
     * @access private
     * @return void
     * @param string $key
     * @param mixed $val
     */
    public function storeVariable($key, $val)
    {
        $this->urlVariables[$key] = $val;
    }

    /**
     * Get section
     *
     * @access public
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Get cache time
     *
     * @access public
     * @return int/bool
     */
    public function getCacheTime()
    {
        return $this->cache;
    }
    public function getCacheName()
    {
        return $this->cacheas;
    }

    /**
     * Get requested control templates name
     *
     * @access public
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get array over what modules should be used when rendering page
     *
     * @access public
     * @return array
     */
    public function getModules($providerName = null)
    {
        if ($providerName !== null) {
            foreach ($this->modules as $m) {
                if (!empty($m['provides']) && $m['provides'] == $providerName) {
                    return $m;
                }
            }
            return null;
        }

        $modules = $this->modules;
        uksort($modules, function ($a, $b) use ($modules) {
            $aSum = $modules[$a]['num'] / 100;
            $bSum = $modules[$b]['num'] / 100;
            if (isset($modules[$a]['provides'])) {
                $aSum--;
            }
            if (isset($modules[$a]['priority'])) {
                $aSum += intval($modules[$a]['priority']);
            }
            if (isset($modules[$b]['provides'])) {
                $bSum--;
            }
            if (isset($modules[$b]['priority'])) {
                $bSum += intval($modules[$b]['priority']);
            }

            return $aSum <=> $bSum;
        });
        return $modules;
    }

    /**
     * Get all options set for section
     *
     * @access public
     * @return array
     * @param array $defaults Provide a set of defaults to use if no value is set
     */
    public function getOptions($defaults = [])
    {
        return $this->options + $defaults;
    }

    /**
     * Set and/or change an option
     *
     * Ex: use it to change or add a config option from within a section
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Get an array of all urlVariables set.
     * These are the variables in a regex url ex. /ads/([0-9]+)/images
     * which are stored with the store="name"-attribute
     */
    public function getUrlVars()
    {
        return $this->urlVariables;
    }

    /**
     * Get an url variable.
     * These are the variables in a regex url ex. /ads/([0-9]+)/images
     * which are stored with the store="name"-attribute
     */
    public function getUrlVar($key)
    {
        if ($this->hasUrlVar($key)) {
            return $this->urlVariables[$key];
        } else {
            return null;
        }
    }

    /**
     * Check if url var exists
     *
     * @return bool
     * @param string $key
     */
    public function hasUrlVar($key)
    {
        return array_key_exists($key, $this->urlVariables);
    }

    /**
     * Get an url variable (DEPRECATED: use getUrlVar())
     *
     * @access public
     * @return mixed
     * @param string $key
     */
    public function getUrlVariable($key)
    {
        return $this->getUrlVar($key);
    }

    /**
     * Fetch url base
     *
     * @access public
     * @return string
     */
    public function getBase()
    {
        return $this->urlBase;
    }
    public function getRoot()
    {
        return $this->urlRoot;
    }

    public function resetRuleConfig()
    {
        // Reset
        $this->options = [];
        $this->modules = [];
        $this->urlVariables = [];
        $this->section = null;
        $this->template = null;
    }

    public function reloadConfigFromDefaultRule()
    {
        if (empty($this->matchedNodes)) {
            throw new Exception('Cannot reload config before initial config is loaded');
        }

        $this->resetRuleConfig();
        $defaultRule = $this->getDefaultRule();
        $this->loadConfigFromConfigNode($defaultRule);
    }
}
