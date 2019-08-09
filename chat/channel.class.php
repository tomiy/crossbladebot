<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;

class Channel extends RateLimit
{
    private $logger;
    private $socket;

    public $name;
    private $ismod;

    public function __construct($logger, $socket, Message $join)
    {
        parent::__construct(20, 30);
        $this->logger = $logger;
        $this->socket = $socket;
        $this->name = $join->params[0];

        $this->logger->info('Joined channel ' . $this->name);
    }

    public function userstate(Message $userstate)
    {
        if ($userstate->tags['mod'] == true && !$this->ismod) {
            $this->logger->info('Setting rate limit to moderator settings');
            $this->setRate(100, 30);
            $this->ismod = true;
        } else if($userstate->tags['mod'] == false && $this->ismod) {
            $this->logger->info('Setting rate limit to user settings');
            $this->setRate(20, 30);
            $this->ismod = false;
        }
    }

    public function send($message)
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->limit();
        $this->socket->send('PRIVMSG ' . $this->name . ' :' . $message . NL);
    }
}
