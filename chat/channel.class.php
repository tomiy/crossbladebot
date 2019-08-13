<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Debug\Logger;

class Channel
{
    use RateLimit;

    private $logger;
    private $socket;

    private $name;
    private $modRequested;

    public function __construct(Logger $logger, Message $join)
    {
        $this->initRate(1, 3);
        $this->logger = $logger;
        $this->name = $join->getParam(0);

        $this->logger->info('Joined channel ' . $this->name);
    }

    public function userstate(Message $userstate): void
    {
        if (!$this->isOp($userstate) && !$this->modRequested) {
            $this->modRequested = true;
            $this->send('Pssst, you should mod me so that i\'m able to use mod commands!');
        }
    }

    public function send(string $message): string
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->limit();
        return 'PRIVMSG ' . $this->name . ' :' . $message;
    }

    public function sendRaw(string $message): string
    {
        return $message;
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
