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

namespace CrossbladeBotTests;

use PHPUnit\Framework\TestCase;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;

/**
 * Test case for the EventHandler class.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class EventHandlerTest extends TestCase
{
    /**
     * Assert that you can create a EventHandler object.
     *
     * @return void
     */
    public function testCanInstantiate(): void
    {
        $this->assertInstanceOf(EventHandler::class, new EventHandler(new Logger()));
    }

    /**
     * Assert that you can register an event and get its unique identifier.
     *
     * @return void
     */
    public function testCanRegisterEvent(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;
        $logger->setLevel(Logger::LEVEL_DEBUG);

        $eventHandler = new EventHandler($logger);
        $uid = $eventHandler->register(
            'test', function () {
                print_r('sweet');
            }
        );

        $this->assertGreaterThanOrEqual(1E9, $uid);
        $this->assertGreaterThanOrEqual($uid, 1E10 - 1);

        $this->assertEquals(
            date('[d/m/y G:i:s] ') .
            'CrossbladeBot\Core\EventHandler:85 [DEBUG] Registered event ' .
            $uid .
            PHP_EOL,
            file_get_contents($logFile)
        );
    }
}
