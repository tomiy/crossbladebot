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

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Debug\Logger;

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
    private $_logger;

    /**
     * Instantiate a new socket object.
     *
     * @param Logger $logger The logger object.
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
     */
    public function connect(): void
    {
        $this->_socket = fsockopen(
            $this->_config->address,
            $this->_config->port,
            $errno,
            $errstr,
            30
        );
        if (!$this->_socket) {
            $this->_logger->error('Couldn\'t create socket');
            die("errno: $errno, errstr: $errstr");
        }
        stream_set_blocking($this->_socket, 0);
        stream_set_timeout($this->_socket, 1);
        $this->_logger->debug('Socket created');
    }

    /**
     * Polls the socket stream for data.
     *
     * @return string The data returned by the stream.
     */
    public function getNext(): string
    {
        if (!$this->_socket) {
            return false;
        }
        $line = fgets($this->_socket);

        if ($line) {
            $this->_logger->debug('> ' . $line);
        }

        return $line;
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
}
