<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Debug\Logger;

class Client extends Configurable
{

    private $logger;
    private $socket;

    public function __construct($socket)
    {
        parent::__construct();

        $this->logger = Logger::getlogger($this);

        $this->socket = $socket;
    }

    public function connect()
    {
        $this->socket->connect();

        $this->socket->send('CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership');
        $this->socket->send('PASS ' . $this->config->password);
        $this->socket->send('NICK ' . $this->config->name);
        $this->socket->send('JOIN #' . $this->config->channel);
    }

    public function serve()
    {
        $this->connect();

        $connected = true;

        $prefix = $this->config->prefix;
        $prefixlen = strlen($prefix);

        $lastping = time();

        while ($connected) {
            if ((time() - $lastping) > 300 or $this->socket === false) {
                $this->logger->info('Restarting connection');
                $this->connect();
                $lastping = time();
            }

            $data = $this->socket->getNext();

            if ($data) {
                print_r($data);
            }
        }
    }
}
