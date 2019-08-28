<?php
/**
 * PHP version 7
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Debug;

use CrossbladeBot\Traits\Configurable;

/**
 * Provides functions to write to a log file with different levels of severity.
 * Only one instance is necessary and can be passed to different classes.
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Logger
{
    use Configurable;

    /**
     * The index corresponding to the error level.
     * Used to log when a critical failure happens in the program.
     *
     * @var integer
     */
    public static $LEVEL_ERROR = 1;
    /**
     * The index corresponding to the warning level.
     * Used to log when a non-blocking failure happens in the program.
     *
     * @var integer
     */
    public static $LEVEL_WARNING = 2;
    /**
     * The index corresponding to the info level.
     * Used to log when any kind of non-failure happens in the program.
     *
     * @var integer
     */
    public static $LEVEL_INFO = 3;
    /**
     * The index corresponding to the debug level.
     * Used to log during debugging.
     * Not to be used when running the program normally.
     *
     * @var integer
     */
    public static $LEVEL_DEBUG = 4;

    /**
     * Instantiate a new logger.
     */
    public function __construct()
    {
        $this->loadConfig();

        file_put_contents($this->_config->log, '');
    }

    /**
     * Write a line in the log file at the given level.
     *
     * @param string  $line  The line to write.
     * @param integer $level The severity level.
     * 
     * @return void
     */
    private function _write(string $line, int $level): void
    {
        if ($level <= $this->_config->level) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $date = date('[d/m/y G:i:s] ');
            $lineNumber = $backtrace[1]['line'];
            $class = $backtrace[2]['class'];
            file_put_contents(
                $this->_config->log,
                trim("$date$class:$lineNumber $line") . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * Write a line in the log file for the debug level.
     *
     * @param string $line The line to write.
     * 
     * @return void
     */
    public function debug(string $line): void
    {
        $this->_write('[DEBUG] ' . $line, self::$LEVEL_DEBUG);
    }

    /**
     * Write a line in the log file for the info level.
     *
     * @param string $line The line to write.
     * 
     * @return void
     */
    public function info(string $line): void
    {
        $this->_write('[INFO] ' . $line, self::$LEVEL_INFO);
    }

    /**
     * Write a line in the log file for the warning level.
     *
     * @param string $line The line to write.
     * 
     * @return void
     */
    public function warning(string $line): void
    {
        $this->_write('[WARNING] ' . $line, self::$LEVEL_WARNING);
    }

    /**
     * Write a line in the log file for the error level.
     *
     * @param string $line The line to write.
     * 
     * @return void
     */
    public function error(string $line): void
    {
        $this->_write('[ERROR] ' . $line, self::$LEVEL_ERROR);
    }
}
