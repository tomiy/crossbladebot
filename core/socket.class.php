<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Debug\Logger;

class Socket extends Configurable
{

    private $socket;
    private $logger;

    public function __construct()
    {
        parent::__construct();

        $this->logger = Logger::getlogger($this);
    }

    public function connect()
    {
        $this->socket = fsockopen($this->config->address, $this->config->port, $errno, $errstr, 30);
        if (!$this->socket) {
            $this->logger->error('Couldn\'t create socket');
            die("errno: $errno, errstr: $errstr");
        }
        stream_set_timeout($this->socket, 1);
        $this->logger->info('Socket created');
    }

    public function getNext()
    {
        if (!$this->socket) return;
        $line = fgets($this->socket);

        if($line) {
            $this->logger->info('> ' . $line);
        }

        return $line;
    }

    public function send($data)
    {
        if (!$this->socket) return;
        fputs($this->socket, $data . NL);

        $this->logger->info('< ' . $data);
    }

    public function close()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
}
