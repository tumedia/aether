<?php // vim:set ts=4 sw=4 et:
/**
 * 
 * Provide a timing and measuring system to aether.
 * Very crude for now
 * 
 * Created: 2009-09-29
 * @author Raymond Julin
 * @package aether
 */

class AetherTimer {
    private $inspectPoints = [];
    private $timers = [];
    private $lastTime = 0;
    private $lastMem = 0;

    /**
     * Methods for doing timer operations
     *
     * @access public
     * @return void
     * @param string $name
     */
    public function timerStart($name) {
        return $this->start($name);
    }
    public function start($name) {
        $this->timers[$name] = [
            [
                'time' => microtime(true),
                'memory' => memory_get_usage()
            ]
        ];
    }
    public function timerEnd($name) {
        $this->end($name);
    }
    public function end($name) {
        $time = microtime(true);
        $first = array_slice($this->timers[$name], 0, 1)[0];
        $ranFor = $time - $first['time'];
        $mem = memory_get_usage();
        $this->timers[$name]['total'] = [
            'time' => $time,
            'memory' => $mem,
            'elapsed' => $ranFor
        ];
    }
    public function timerTick($name, $point) {
        $this->tick($name, $point);
    }
    public function tick($name, $point) {
        $time = microtime(true);
        $prev = end($this->timers[$name]);
        $this->timers[$name][$point] = [
            'time' => $time,
            'memory' => memory_get_usage(),
            'elapsed' => $time - $prev['time']
        ];
    }
    public function all() {
        return $this->timers;
    }
}
