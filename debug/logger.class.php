<?php

namespace CrossbladeBot\Debug;

use CrossbladeBot\Traits\Configurable;
use stdClass;

class Logger extends Configurable
{

    public static $LEVEL_ERROR = 1;
    public static $LEVEL_WARNING = 2;
    public static $LEVEL_INFO = 3;

    private $class;

    public static function init() {
        $log = new static(new stdClass());
        $log->clear();
    }

    public static function getlogger($class) {
        return new static($class);
    }

    private function __construct($class)
    {
        parent::__construct();

        $this->class = (new \ReflectionClass($class))->getShortName();
    }

    private function clear() {
        file_put_contents($this->config->log, '');
    }

    private function write($line, $level)
    {

        if ($level <= $this->config->level) {
            $date = date('[d/m/y G:i:s] ');
            $at = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];
            file_put_contents($this->config->log, trim("$date{$this->class}:$at $line") . PHP_EOL, FILE_APPEND);
        }
    }

    public function info($line)
    {
        $this->write('[INFO] ' . $line, self::$LEVEL_INFO);
    }

    public function warning($line)
    {
        $this->write('[WARNING] ' . $line, self::$LEVEL_WARNING);
    }

    public function error($line)
    {
        $this->write('[ERROR] ' . $line, self::$LEVEL_ERROR);
    }
}
