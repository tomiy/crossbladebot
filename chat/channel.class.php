<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;

class Channel extends RateLimit
{
    private $logger;

    public $name;
    private $ismod;

    public function __construct($logger, Message $join)
    {
        parent::__construct(20, 30);
        $this->logger = $logger;
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

    public function send($message, $socket)
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->limit();
        $socket->send('PRIVMSG ' . $this->name . ' :' . $message . NL);
    }
}
