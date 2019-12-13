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

namespace CrossbladeBot\Service\MessageHandler;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

/**
 * Provides function to handle a type of message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
abstract class AbstractMessageHandler
{
    /**
     * The logger object.
     *
     * @var Logger
     */
    protected Logger $logger;
    /**
     * The event handler holding the component events.
     *
     * @var EventHandler;
     */
    protected EventHandler $eventHandler;
    /**
     * The client object.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * The command prefix.
     *
     * @var string
     */
    protected string $prefix;
    /**
     * The length of the prefix.
     *
     * @var int
     */
    protected int $prefixLen;

    /**
     * A map of types and callbacks.
     *
     * @var array
     */
    protected array $callbackMap;

    /**
     * Instantiate a new message handler.
     *
     * @param Logger       $logger       The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client       $client       The client object.
     */
    public function __construct(
        Logger $logger, EventHandler $eventHandler, Client $client
    ) {
        $this->logger = $logger;
        $this->eventHandler = $eventHandler;
        $this->client = $client;

        $this->prefix = $client->getConfig()->prefix;
        $this->prefixLen = strlen($this->prefix);
    }

    /**
     * Handle a message.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        if (array_key_exists($message->getType(), $this->callbackMap)) {
            $method = $this->callbackMap[$message->getType()];
            if (!empty($method) && method_exists($this, $method)) {
                $this->{$method}($message);
            }
            return;
        }
        $this->cantParse($message);
    }

    /**
     * Handles the part messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function part(Message $message): void
    {
        $channel = $this->client->getChannel($message->getChannel());
        $channel->part();
        $this->eventHandler->trigger('part', $channel);
    }

    /**
     * Handles the join messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function join(Message $message): void
    {
        if ($this->client->isMe($message->getUser())) {
            $channel = new Channel($this->logger, $message);
            $this->client->addChannel($channel);
            $this->logger->debug(
                'Added channel ' . $channel->getName() . ' to client'
            );
            $this->eventHandler->trigger('join', $channel);
            return;
        }
        //TODO: another user joined
    }

    /**
     * Handles the chat messages & commands.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function privMsg(Message $message): void
    {
        if ($this->client->isMe($message->getUser())) {
            return;
        }
        $channel = $this->client->getChannel($message->getChannel());
        if (substr(
            $message->getMessage(), 0, $this->prefixLen
        ) === $this->prefix
        ) {
            $args = explode(' ', $message->getMessage());
            $message->setCommand(
                substr(array_shift($args), $this->prefixLen)
            );
            $this->eventHandler->trigger('command', $message, $channel, $args);
            return;
        }
        $this->eventHandler->trigger('message', $message, $channel);
    }

    /**
     * Handles the user state messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function userState(Message $message): void
    {
        $channel = $this->client->getChannel($message->getParam(0));
        if ($channel->isParted() === true) {
            $this->client->removeChannel($channel);
            $this->logger->debug(
                'Removed channel ' . $channel->getName() . ' from client'
            );
            unset($channel);
            return;
        }
        $channel->userState($message);
    }

    /**
     * Handles the notice messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function notice(Message $message): void
    {
        foreach ( //TODO: rework
            [
            'Login unsuccessful',
            'Login authentication failed',
            'Error logging in',
            'Improperly formatted auth',
            'Invalid NICK'
            ] as $needle) {
            if (strpos($message->getMessage(), $needle) !== false) {
                $this->logger->error('Potential auth failure: ' . $needle);
                $this->client->setConnected(false);
                break;
            }
        }
    }

    /**
     * Handles the user notice messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function userNotice(Message $message): void
    {
        switch ($message->getId()) {
        default:
            //TODO: handle
            break;
        }
    }

    /**
     * Log the message that can't be parsed at warning level.
     *
     * @param Message $message The message to log.
     *
     * @return void
     */
    protected function cantParse(Message $message): void
    {
        $this->logger->warning(
            'Could not parse message: ' . $message->getRaw()
        );
    }
}
