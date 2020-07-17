<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace crossbladebot\chat;

use crossbladebot\basic\RateLimit;
use crossbladebot\debug\Logger;
use crossbladebot\service\Queue;

/**
 * Handles a channel specific rate limit, and processes userstates and messages.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Channel extends Queue
{
    use RateLimit;

    /**
     * The logger object
     *
     * @var Logger
     */
    private Logger $_logger;

    /**
     * The name of the channel.
     *
     * @var string
     */
    private string $_name;
    /**
     * Whether the channel has been parted from.
     *
     * @var bool
     */
    private bool $_part;
    /**
     * Whether the client has requested the mod status.
     *
     * @var bool
     */
    private bool $_modRequested;

    /**
     * Instantiate a channel.
     *
     * @param Message $join The join message received from the IRC.
     */
    public function __construct(Message $join)
    {
        $this->initRate(1, 3);
        $this->_logger = Logger::getInstance();
        $this->_name = $join->getParam(0);
        $this->_part = false;

        $this->_logger->info('Joined channel ' . $this->_name);
    }

    /**
     * Destroys the channel. Used only for logging.
     */
    public function __destruct()
    {
        $this->_logger->info('Parted channel ' . $this->_name);
    }

    /**
     * Handles an userstate message.
     *
     * @param Message $userState The message to handle.
     *
     * @return void
     */
    public function userState(Message $userState): void
    {
        if (!$this->_isOp($userState) && !$this->_modRequested) {
            $this->_modRequested = true;
            $this->send('Pssst, you should mod me so i can use mod commands!');
        }
    }

    /**
     * Checks if a message is sent from someone that is mod or owner of the channel.
     *
     * @param Message $message The message to check.
     *
     * @return bool Whether the user is mod or owner.
     */
    private function _isOp(Message $message): bool
    {
        return $this->_isMod($message) || $this->_isBroadcaster($message);
    }

    /**
     * Checks if a message is sent from a moderator of the channel.
     *
     * @param Message $message The message to check.
     *
     * @return bool Whether the user is a mod.
     */
    private function _isMod(Message $message): bool
    {
        return $message->getTag('mod') == 1;
    }

    /**
     * Checks if a message is sent from the owner of the channel.
     *
     * @param Message $message The message to check.
     *
     * @return bool Whether the user is the owner.
     */
    private function _isBroadcaster(Message $message): bool
    {
        return $this->_name === '#' . $message->getUser();
    }

    /**
     * Send a chat message to the channel.
     *
     * @param string $message The message to send.
     *
     * @return void
     */
    public function send(string $message): void
    {
        $this->_logger->debug('Sending message: "' . trim($message) . '" to channel: ' . $this->_name);
        $this->sendRaw('PRIVMSG ' . $this->_name . ' :' . $message);
    }

    /**
     * Queues a message in the channel queue.
     *
     * @param string $message The message to queue.
     *
     * @return void
     */
    public function sendRaw(string $message): void
    {
        $this->_logger->debug('Sending raw: "' . trim($message) . '" to channel: ' . $this->_name);
        $this->enqueue([$message]);
    }

    /**
     * Flags the channel to be parted.
     *
     * @return void
     */
    public function part(): void
    {
        $this->_part = true;
    }

    /**
     * Check the user level of the user sending a message.
     * 0 = user, 1 = moderator, 2 = owner
     *
     * @param Message $message The message to check.
     *
     * @return integer The index corresponding to the user level.
     */
    public function getUserLevel(Message $message): int
    {
        $userLevel = 0;
        if ($this->_isOp($message)) {
            $userLevel++;
        }
        if ($this->_isBroadcaster($message)) {
            $userLevel++;
        }
        return $userLevel;
    }

    /**
     * Get the name of the channel.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Whether the channel is parted from.
     *
     * @return bool
     */
    public function isParted(): bool
    {
        return $this->_part;
    }
}
