<?php

namespace Aether\Response;

use Exception;

/**
 * Textual response
 *
 * Created: 2007-02-05
 * @author Raymond Julin
 * @package aether.lib
 */
class Text extends Response
{
    /**
     * Hold text string for output
     * @var string
     */
    private $out = '';
    private $contentType;

    /**
     * Constructor
     *
     * @access public
     * @param string $output
     */
    public function __construct($output, $contentType = null)
    {
        $this->out = $output;
        $this->contentType = $contentType;
    }

    /**
     * Draw text response. Echoes out the response
     *
     * @access public
     * @return void
     * @param \Aether\ServiceLocator $sl
     */
    public function draw($sl)
    {
        if (session_id() !== '') {
            $_SESSION['wasGoingTo'] = $_SERVER['REQUEST_URI'];
        }

        $tpl = $sl->getTemplate();

        $timer = $sl->get('timer');

        if ($timer && $tpl->templateExists('debugBar.tpl')) {
            // Timer
            $timer->end('aether_main');
            // Replace into out content
            //$tpl->selectTemplate('debugBar');
            $timers = $timer->all();
            foreach ($timers as $key => $tr) {
                foreach ($tr as $k => $t) {
                    if (!array_key_exists('elapsed', $t)) {
                        $t['elapsed'] = 0;
                    }
                    $timers[$key][$k]['elapsed'] = number_format($t['elapsed'], 4);
                    // Format memory
                    if (isset($t['memory'])) {
                        $memLen = strlen($t['memory']);
                        $memUse = $t['memory'];
                        if ($memLen > 9) {
                            $memUse = round($memUse / (1000*1000*1000), 1) . "GB";
                        }
                        if ($memLen > 6) {
                            $memUse = round($memUse / (1000*1000), 1) . "MB";
                        } elseif ($memLen > 3) {
                            $memUse = round($memUse / (1000), 1) . "KB";
                        }
                        $timers[$key][$k]['mem_use'] = $memUse;
                    }
                }
            }
            $tpl->set('timers', $timers);
            try {
                $out = $tpl->fetch('debugBar.tpl');
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $out = str_replace(
                "<!--INSERTIONPOINT-->",
                $out,
                $this->out
            );
        } else {
            // No timing, we're in prod
            $out = $this->out;
        }
        if ($this->contentType) {
            header("Content-Type: {$this->contentType}; charset=UTF-8");
        }
        echo $out;
    }

    /**
     * Return instead of echo
     *
     * @access public
     * @return string
     */
    public function get()
    {
        return $this->out;
    }
}
