<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace crossbladebottests;

use crossbladebot\debug\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Logger class.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class LoggerTest extends TestCase
{
    /**
     * Assert that you can create a Logger object.
     *
     * @return void
     */
    public function testCanInstantiate(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertFileExists($logFile);
        $this->assertFileIsWritable($logFile);
        $this->assertEmpty(file_get_contents($logFile));
    }

    /**
     * Assert that you can log a line at the debug level.
     *
     * @return void
     */
    public function testCanLogDebug(): void
    {
        list($expected, $actual) = $this->_testCanLog('debug');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that you can log a line at the info level.
     *
     * @return void
     */
    public function testCanLogInfo(): void
    {
        list($expected, $actual) = $this->_testCanLog('info');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that you can log a line at the warning level.
     *
     * @return void
     */
    public function testCanLogWarning(): void
    {
        list($expected, $actual) = $this->_testCanLog('warning');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that you can log a line at the error level.
     *
     * @return void
     */
    public function testCanLogError(): void
    {
        list($expected, $actual) = $this->_testCanLog('error');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that you can log a line.
     *
     * @param string $level The level to test.
     *
     * @return string
     */
    private function _testCanLog(string $level): array
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;
        $logger->setLevel(Logger::LEVEL_DEBUG);
        $logger->$level('test');

        $line = __LINE__ - 2;
        $date = date('[d/m/y G:i:s] ');

        return [
            trim(
                "$date".__CLASS__.":$line [" . strtoupper($level) . "] test"
            ) . PHP_EOL,
            file_get_contents($logFile)
        ];
    }
}
