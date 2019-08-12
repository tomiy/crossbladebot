<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Debug\Logger;

class Channel extends RateLimit
{
    private $logger;
    private $socket;

    private $name;
    private $isop;
    private $modRequested;

    public function __construct(Logger $logger, Socket $socket, Message $join)
    {
        parent::__construct(20, 30);
        $this->logger = $logger;
        $this->socket = $socket;
        $this->name = $join->getParam(0);

        $this->logger->info('Joined channel ' . $this->name);
    }

    public function userstate(Message $userstate): void
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

    public function send(string $message): void
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->limit();
        $this->socket->send('PRIVMSG ' . $this->name . ' :' . $message);
    }

    public function sendRaw(string $message): void
    {
        $this->socket->send($message);
    }

    private function isOp(Message $message): bool
    {
        return $this->isMod($message) || $this->isBroadcaster($message);
    }

    private function isMod(Message $message): bool
    {
        return $message->getTag('mod') == 1;
    }

    private function isBroadcaster(Message $message): bool
    {
        return $this->name === '#' . $message->getUser();
    }

    public function getUserLevel(Message $message): int
    {
        $userlevel = 0;
        if ($this->isOp($message)) {
            $userlevel++;
        }
        if ($this->isBroadcaster($message)) {
            $userlevel++;
        }
        return $userlevel;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
