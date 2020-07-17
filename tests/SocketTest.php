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

use crossbladebot\core\Socket;
use crossbladebot\debug\Logger;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
     * @throws ReflectionException
     */
    public function testCanInstantiate(): void
    {
        $this->assertInstanceOf(Socket::class, new Socket());
    }

    /**
     * Assert that the socket throws an Exception with an invalid address.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testConnectionWithInvalidAddress(): void
    {
        $logger = Logger::getInstance();
        $logger->clearLogFile();
        $logFile = $logger->getConfig('log');

        $socket = new Socket();
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
     * @throws ReflectionException
     */
    public function testConnectionWithInvalidPort(): void
    {
        $logger = Logger::getInstance();
        $logger->clearLogFile();
        $logFile = $logger->getConfig('log');

        $socket = new Socket();
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
     * @throws Exception
     */
    public function testConnection(): void
    {
        $logger = Logger::getInstance();
        $logger->clearLogFile();
        $logFile = $logger->getConfig('log');
        $logger->setLevel(Logger::LEVEL_DEBUG);

        try {
            $socket = new Socket();
        } catch (ReflectionException $e) {
            $this->fail('ReflectionException');
        }
        $socket->connect();

        $this->assertEquals(
            date('[d/m/y G:i:s] ') .
            'crossbladebot\core\Socket:91 [DEBUG] Socket created' .//TODO: change bad test data
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
        'crossbladebot\core\Socket:86 [ERROR] Couldn\'t create socket' .//TODO: change bad test data
        PHP_EOL;
    }
}
