<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Service\Queue;

class Channel extends Queue
{
    use RateLimit;

    private $logger;
    private $socket;

    private $name;
    private $part;
    private $modRequested;

    public function __construct(Logger $logger, Message $join)
    {
        $this->initRate(1, 3);
        $this->logger = $logger;
        $this->name = $join->getParam(0);
        $this->part = false;

        $this->logger->info('Joined channel ' . $this->name);
    }

    private function __destruct()
    {
        $this->logger->info('Parted channel ' . $this->name);
    }

    public function userstate(Message $userstate): void
    {
        if (!$this->isOp($userstate) && !$this->modRequested) {
            $this->modRequested = true;
            $this->send('Pssst, you should mod me so that i\'m able to use mod commands!');
        }
    }

    public function part(): void
    {
        $this->part = true;
    }

    public function send(string $message): void
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->sendRaw('PRIVMSG ' . $this->name . ' :' . $message);
    }

    public function sendRaw(string $message): void
    {
        $this->enqueue([$message]);
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

    public function isParted(): bool
    {
        return $this->part;
    }
}
