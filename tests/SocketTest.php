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

use Exception;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;

/**
 * Test case for the Socket class.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class SocketTest extends TestCase
{
    /**
     * Assert that you can create a Socket object.
     *
     * @return void
     */
    public function testCanInstantiate(): void
    {
        $this->assertInstanceOf(Socket::class, new Socket(new Logger()));
    }

    /**
     * Assert that the socket throws an Exception with an invalid address.
     *
     * @return void
     */
    public function testConnectionWithInvalidAddress(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;

        $socket = new Socket($logger);
        $socket->setAddress('invalid');

        try {
            $socket->connect();
        } catch (Exception $e) {
            $this->assertEquals(
                $this->_socketErrorMessage(),
                file_get_contents($logFile)
            );
            return;
        }

        $this->fail('Exception was not raised');
    }

    /**
     * Assert that the socket throws an Exception with an invalid port.
     *
     * @return void
     */
    public function testConnectionWithInvalidPort(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;

        $socket = new Socket($logger);
        $socket->setPort(777);

        try {
            $socket->connect();
        } catch (Exception $e) {
            $this->assertEquals(
                $this->_socketErrorMessage(),
                file_get_contents($logFile)
            );
            return;
        }

        $this->fail('Exception was not raised');
    }

    /**
     * Assert that the socket can connect.
     *
     * @return void
     */
    public function testConnection(): void
    {
        $logger = new Logger();
        $logFile = $logger->getConfig()->log;
        $logger->setLevel(Logger::$LEVEL_DEBUG);

        $socket = new Socket($logger);
        $socket->connect();

        $this->assertEquals(
            date('[d/m/y G:i:s] ') .
            'CrossbladeBot\Core\Socket:93 [DEBUG] Socket created' .
            PHP_EOL,
            file_get_contents($logFile)
        );
    }

    /**
     * Returns the expected error message on an Exception coming from the socket.
     *
     * @return string
     */
    private function _socketErrorMessage(): string
    {
        return 
        date('[d/m/y G:i:s] ') .
        'CrossbladeBot\Core\Socket:83 [ERROR] Couldn\'t create socket' .
        PHP_EOL;
    }
}
