<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Debug\Logger;

/**
 * Reads and writes from a socket at the given adress and port.
 */
class Socket
{
    use Configurable;

    /**
     * The socket stream resource.
     *
     * @var resource
     */
    private $socket;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->loadConfig();

        $this->logger = $logger;
    }

    /**
     * Creates the socket stream from the config, with a timeout of 1s.
     *
     * @return void
     */
    public function connect(): void
    {
        $this->socket = fsockopen($this->config->address, $this->config->port, $errno, $errstr, 30);
        if (!$this->socket) {
            $this->logger->error('Couldn\'t create socket');
            die("errno: $errno, errstr: $errstr");
        }
        stream_set_blocking($this->socket, 0);
        stream_set_timeout($this->socket, 1);
        $this->logger->info('Socket created');
    }

    /**
     * Polls the socket stream for data.
     *
     * @return string The data returned by the stream if there is something to return.
     */
    public function getNext(): string
    {
        if (!$this->socket) {
            return false;
        }
        $line = fgets($this->socket);

        if ($line) {
            $this->logger->info('> ' . $line);
        }

        return $line;
    }

    /**
     * Sends data to the socket stream.
     *
     * @param string $data The data to send.
     * @return void
     */
    public function send(string $data): void
    {
        if (!$this->socket) {
            return;
        }
        fputs($this->socket, $data . NL);

        $this->logger->info('< ' . $data);
    }

    /**
     * Terminates the socket stream.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
}
