<?php // vim:set ts=4 sw=4 et:

/**
 * Base class for command line scripting in Aether
 * Sorts out command line option handling for you
 * as this can be a bit cumbersome to do manually each time
 * Usage:
 *<code>
 *class MyCLI extends AetherCLI {
 *    protected $allowedOptions = array('u'=>'update');
 *    public function run() {
 *        $opt = $this->getOption('update'); // Will fetch textual value from -u/--update=foo
 *        doSomeAction($opt);
 *    }
 *}
 *</code>
 * Help files are automaticaly presented by AetherCLI when no options are
 * sent to the script or the -h/--help is sent as the only argument
 * Help files are to be written in a textfile called $appFile.substr('php','help')
 * 
 * Created: 2008-12-12
 * @author Raymond Julin
 * @package aether
 */

abstract class AetherCLI {
    
    /**
     * Define legal options for this program.
     * All other passed options will be ignored
     * Options are supplied on this form:
     * array('shortName' => 'longName');
     * Example:
     * array('a' => 'action', 'p' => 'path');
     * @var array
     */
    protected $allowedOptions = array();
    
    /**
     * Hold all parsed options
     * @var array
     */
    protected $options = array();
    
    /**
     * Start time
     * @var  int
     */
    protected $startTime;
    protected $endTime;
    
    /**
     * Handle option parsing
     *
     * @access 
     * @return 
     */
    public function __construct() {
        echo "Start time [".date('Y-m-d H:i:s')."]\n";
        $this->startTime = $this->getMicroTime();
        $this->options = $this->parseOptions($_SERVER['argv']);
        $this->mixinHelpSupport();
        // Run help file if help required
        if (($this->hasOptions(array('help')) AND count($this->options) == 1)
            OR count($this->options) == 0) {
            $this->displayHelpFile();
        }
    }

    
    
    /**
     * Run the app
     *
     * @access public
     * @return void
     */
    abstract public function run();
    
    /**
     * Mix in support for --help/-h always
     * And let it act upon this option without using
     * the extending script
     *
     * @access protected
     * @return void
     */
    protected function mixinHelpSupport() {
        if (array_key_exists('h', $this->allowedOptions) == false)
            $this->allowedOptions['h'] = 'help';
        if (array_key_exists('h', $this->options) == false)
            $this->options['help'] = '';
    }
    
    /**
     * Display the help file following a cli app
     *
     * @access protected
     * @return void
     */
    protected function displayHelpFile() {
        global $argv;
        $file = substr_replace(realpath($argv[0]), 'help', -3);
        if (file_exists($file)) {
            $content = file_get_contents($file);
        }
        else {
            // Use default help file for AetherCLI
            $path = pathinfo(__FILE__, PATHINFO_DIRNAME);
            $content = file_get_contents($path . '/lib/AetherCLI.help');
        }
        echo $content;
    }
    
    /**
     * Parse options from command line
     * Only defined options from allowedOptions will be taken into
     * consideration. All others supplied info will be overlooked
     *
     * @access private
     * @param array $args
     * @return array
     */
    protected function parseOptions($args) {
        $options = array();
        if (is_array($args) AND count($args) > 0) {
            foreach ($args as $arg) {
                // Is valid option
                if (preg_match('/(--[a-z]+|-[a-z]{1})(?>=[a-z-_\/]+)?/', $arg))  {
                    $parts = explode('=', $arg);
                    $name = preg_replace('/[-]{1,2}/', '', $parts[0]);

                    // Always use long name in returned array
                    if (strlen($name) == 1 AND 
                        array_key_exists($name,$this->allowedOptions)) {
                        // Translate to long name for short option
                        $name = $this->allowedOptions[$name];
                    }
                    // Only use allowed options
                    if (in_array($name, $this->allowedOptions)) {
                        if (array_key_exists(1, $parts))
                            $options[$name] = $parts[1];
                        else
                            $options[$name] = '';
                    }
                }
            }
        }
        return $options;
    }
    
    /**
     * Return a single options value by name
     *
     * @access protected
     * @return mixed
     * @param string $key
     */
    public function getOption($key) {
        if (array_key_exists($key, $this->options))
            return $this->options[$key];
    }
    
    /**
     * Verify CLI job has all options
     *
     * @access public
     * @return boolean
     * @param array $opts As long opts
     */
    public function hasOptions($opts) {
        foreach ($opts as $o) {
            if (!array_key_exists($o, $this->options))
                return false;
        }
        return true;
    }

    /**
     * Verify CLI job has one option 
     * 
     * @param mixed $opt Option to check for
     * @access public
     * @return boolean 
     */
    public function hasOption($opt) {
        return $this->hasOptions(array($opt));
    }
    
    /**
     * Return timing information
     *
     * @access protected
     * @return int
     */
	protected function getMicroTime() { 
		list($usec, $sec) = explode(" ", microtime()); 
		return ((float)$usec + (float)$sec); 
	}
    protected function getRunTime() {
        return $this->getMicroTime() - $this->startTime;
    }
}
