<?php

namespace CrossbladeBot\Debug;

use CrossbladeBot\Traits\Configurable;

class Logger
{
    use Configurable;

    public static $LEVEL_ERROR = 1;
    public static $LEVEL_WARNING = 2;
    public static $LEVEL_INFO = 3;

    public function __construct()
    {
        $this->loadConfig();

        file_put_contents($this->config->log, '');
    }

    private function write(string $line, int $level): void
    {

        if ($level <= $this->config->level) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $date = date('[d/m/y G:i:s] ');
            $at = $backtrace[1]['line'];
            $class = $backtrace[2]['class'];
            file_put_contents($this->config->log, trim("$date$class:$at $line") . PHP_EOL, FILE_APPEND);
        }
    }

    public function info(string $line): void
    {
        $this->write('[INFO] ' . $line, self::$LEVEL_INFO);
    }

    public function warning(string $line): void
    {
        $this->write('[WARNING] ' . $line, self::$LEVEL_WARNING);
    }

    public function error(string $line): void
    {
        $this->write('[ERROR] ' . $line, self::$LEVEL_ERROR);
    }
}
