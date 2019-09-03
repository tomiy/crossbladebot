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
        $this->assertInstanceOf(Logger::class, new Logger());
    }
}
