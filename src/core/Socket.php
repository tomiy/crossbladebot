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

namespace crossbladebot\core;

use crossbladebot\basic\Configuration;
use crossbladebot\debug\Logger;
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

    /**
     * The socket stream resource.
     *
     * @var resource
     */
    private $_socket;
    /**
     * The address of the stream.
     * 
     * @var string
     */
    private string $_address;
    /**
     * The port of the stream.
     *
     * @var int
     */
    private int $_port;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private Logger $_logger;

    /**
     * Instantiate a new socket object.
     *
     * @throws ReflectionException
     */
    public function __construct()
    {
        $config = Configuration::load('Socket.json');
        $this->setAddress($config->get('address'));
        $this->setPort($config->get('port'));
        
        $this->_logger = Logger::getInstance();
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

        $hostname = parse_url($this->getAddress(), PHP_URL_HOST);

        if ($hostname) {
            $hostIp = gethostbyname($hostname);

            if ($hostIp !== $this->getAddress()) {
                $this->setSocket(
                    @fsockopen(
                        $this->getAddress(),
                        $this->getPort(),
                        $errorCode,
                        $errorString,
                        5
                    )
                );
            }
        }

        if (!$this->isConnected()) {
            $this->_logger->error('Couldn\'t create socket');
            throw new Exception('Couldn\'t create socket: ' . $errorCode . ' ' . $errorString);
        }
        stream_set_blocking($this->getSocket(), false);
        stream_set_timeout($this->getSocket(), 1);
        $this->_logger->debug('Socket created');
    }

    /**
     * Checks if the socket handle exists and is active.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return is_resource($this->getSocket());
    }

    /**
     * Polls the socket stream for data.
     *
     * @return string The data returned by the stream.
     */
    public function getNext(): ?string
    {
        if (!$this->getSocket()) {
            return null;
        }
        
        $line = fgets($this->getSocket());

        if (is_string($line)) {
            $this->_logger->debug('> ' . $line);
            return $line;
        }
        
        return null;
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
        if (!$this->getSocket()) {
            return;
        }
        fputs($this->getSocket(), $data . NL);

        $this->_logger->debug('< ' . $data);
    }

    /**
     * Terminates the socket stream.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->getSocket()) {
            fclose($this->getSocket());
        }
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->_address;
    }

    /**
     * @return number
     */
    public function getPort(): int
    {
        return $this->_port;
    }

    /**
     * @param resource $_socket
     */
    public function setSocket($_socket): void
    {
        $this->_socket = $_socket;
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
        $this->_address = $address;
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
        $this->_port = $port;
    }
}
