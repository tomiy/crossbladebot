<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;

class Channel extends RateLimit
{
    private $logger;
    private $socket;

    public $name;
    private $isop;
    private $modRequested;

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
        if ($this->isOp($userstate) && !$this->isop) {
            $this->isop = true;
            $this->logger->info('Setting rate limit to moderator settings');
            $this->setRate(100, 30);
        }
        if (!$this->isOp($userstate)) {
            if ($this->isop) {
                $this->isop = false;
                $this->logger->info('Setting rate limit to user settings');
                $this->setRate(20, 30);
            }
            if (!$this->modRequested) {
                $this->modRequested = true;
                $this->send('Pssst, you should mod me so that i\'m able to send stuff with more ease!');
            }
        }
    }

    public function send($message)
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->limit();
        $this->socket->send('PRIVMSG ' . $this->name . ' :' . $message);
    }

    public function sendRaw($message) {
        $this->socket->send($message);
    }

    private function isOp($message) {
        return $this->isMod($message) || $this->isBroadcaster($message);
    }

    private function isMod($message) {
        return $message->tags['mod'];
    }

    private function isBroadcaster($message) {
        return $this->name === '#' . strtolower($message->tags['display-name']);
    }

    public function getUserLevel($message) {
        $userlevel = 0;
        if($this->isOp($message)) {
            $userlevel++;
        }
        if($this->isBroadcaster($message)) {
            $userlevel++;
        }
        return $userlevel;
    }
}
