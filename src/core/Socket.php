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

namespace CrossbladeBot\Core;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Traits\Configurable;
use Exception;
use ReflectionException;

/**
 * Reads and writes from a socket at the given adress and port.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Socket
{
    use Configurable;

    /**
     * The socket stream resource.
     *
     * @var resource
     */
    private $_socket;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private Logger $_logger;

    /**
     * Instantiate a new socket object.
     *
     * @param Logger $logger The logger object.
     * @throws ReflectionException
     */
    public function __construct(Logger $logger)
    {
        $this->loadConfig();

        $this->_logger = $logger;
    }

    /**
     * Creates the socket stream from the config, with a timeout of 1s.
     *
     * @return void
     * @throws Exception
     */
    public function connect(): void
    {
        $errorCode = $errorString = $hostIp = null;

        $hostname = parse_url($this->_config->address, PHP_URL_HOST);

        if ($hostname) {
            $hostIp = gethostbyname($hostname);

            if ($hostIp !== $this->_config->address) {
                $this->_socket = @fsockopen(
                    $this->_config->address,
                    $this->_config->port,
                    $errorCode,
                    $errorString,
                    5
                );
            }
        }

        if (!$this->isConnected()) {
            $this->_logger->error('Couldn\'t create socket');
            throw new Exception('Couldn\'t create socket: ' . $errorCode . ' ' . $errorString);
        }
        stream_set_blocking($this->_socket, false);
        stream_set_timeout($this->_socket, 1);
        $this->_logger->debug('Socket created');
    }

    /**
     * Checks if the socket handle exists and is active.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return is_resource($this->_socket);
    }

    /**
     * Polls the socket stream for data.
     *
     * @return string The data returned by the stream.
     */
    public function getNext(): string
    {
        if (!$this->_socket) {
            return (string)false;
        }
        $line = fgets($this->_socket);

        if ($line) {
            $this->_logger->debug('> ' . $line);
        }

        return (string)$line;
    }

    /**
     * Sends data to the socket stream.
     *
     * @param string $data The data to send.
     *
     * @return void
     */
    public function send(string $data): void
    {
        if (!$this->_socket) {
            return;
        }
        fputs($this->_socket, $data . NL);

        $this->_logger->debug('< ' . $data);
    }

    /**
     * Terminates the socket stream.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->_socket) {
            fclose($this->_socket);
        }
    }

    /**
     * Set the address of the stream.
     *
     * @param string $address The address of the stream.
     *
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->_config->address = $address;
    }

    /**
     * Set the port of the stream.
     *
     * @param int $port The port of the stream.
     *
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->_config->port = $port;
    }
}
