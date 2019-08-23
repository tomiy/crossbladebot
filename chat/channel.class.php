<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Service\Queue;

/**
 * Handles a channel specific rate limit, and processes userstates and messages.
 */
class Channel extends Queue
{
    use RateLimit;

    /**
     * The logger object
     *
     * @var Logger
     */
    private $logger;

    /**
     * The name of the channel.
     *
     * @var string
     */
    private $name;
    /**
     * Whether the channel has been parted from.
     *
     * @var boolean
     */
    private $part;
    /**
     * Whether the client has requested the mod status.
     *
     * @var boolean
     */
    private $modRequested;

    public function __construct(Logger $logger, Message $join)
    {
        $this->initRate(1, 3);
        $this->logger = $logger;
        $this->name = $join->getParam(0);
        $this->part = false;

        $this->logger->info('Joined channel ' . $this->name);
    }

    public function __destruct()
    {
        $this->logger->info('Parted channel ' . $this->name);
    }

    /**
     * Handles an userstate message.
     *
     * @param Message $userstate The message to handle.
     * @return void
     */
    public function userstate(Message $userstate): void
    {
        if (!$this->isOp($userstate) && !$this->modRequested) {
            $this->modRequested = true;
            $this->send('Pssst, you should mod me so that i\'m able to use mod commands!');
        }
    }

    /**
     * Flags the channel to be parted.
     *
     * @return void
     */
    public function part(): void
    {
        $this->part = true;
    }

    /**
     * Send a chat message to the channel.
     *
     * @param string $message The message to send.
     * @return void
     */
    public function send(string $message): void
    {
        $this->logger->info('Sending message: "' . trim($message) . '" to channel: ' . $this->name);
        $this->sendRaw('PRIVMSG ' . $this->name . ' :' . $message);
    }

    /**
     * Queues a message in the channel queue.
     *
     * @param string $message The message to queue.
     * @return void
     */
    public function sendRaw(string $message): void
    {
        $this->enqueue([$message]);
    }

    /**
     * Checks if a message is sent from someone that is moderator or owner of the channel.
     *
     * @param Message $message The message to check.
     * @return boolean Whether the user is mod or owner.
     */
    private function isOp(Message $message): bool
    {
        return $this->isMod($message) || $this->isBroadcaster($message);
    }

    /**
     * Checks if a message is sent from a moderator of the channel.
     *
     * @param Message $message The message to check.
     * @return boolean Whether the user is a mod.
     */
    private function isMod(Message $message): bool
    {
        return $message->getTag('mod') == 1;
    }

    /**
     * Checks if a message is sent from the owner of the channel.
     *
     * @param Message $message The message to check.
     * @return boolean Whether the user is the owner.
     */
    private function isBroadcaster(Message $message): bool
    {
        return $this->name === '#' . $message->getUser();
    }

    /**
     * Check the user level of the user sending a message.
     * 0 = user, 1 = moderator, 2 = owner
     *
     * @param Message $message The message to check.
     * @return integer The index corresponding to the user level.
     */
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
