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

use PHPUnit\Framework\TestCase;

use CrossbladeBot\Debug\Logger;

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
     * Assert that you can log a line at the info level.
     *
     * @return void
     */
    public function testCanLogInfo(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;
        $logger->info('test');

        $line = __LINE__ - 2;
        $date = date('[d/m/y G:i:s] ');

        $this->assertEquals(
            trim("$date".__CLASS__.":$line [INFO] test") . PHP_EOL,
            file_get_contents($logFile)
        );
    }
}
