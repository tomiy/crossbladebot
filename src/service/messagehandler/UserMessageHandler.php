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

namespace crossbladebot\service\messagehandler;

use crossbladebot\chat\Channel;
use crossbladebot\chat\Message;
use crossbladebot\core\Client;
use crossbladebot\core\EventHandler;
use crossbladebot\debug\Logger;

/**
 * Provides function to handle a user message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class UserMessageHandler extends AbstractMessageHandler
{
    /**
     * Initialize the callback map for handling user messages.
     *
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(EventHandler $eventHandler, Client $client)
    {
        parent::__construct($eventHandler, $client);

        $this->callbackMap = [
            '353' => null,
            '366' => null,
            'JOIN' => 'join',
            'PART' => 'part',
            'WHISPER' => null,
            'PRIVMSG' => 'privMsg'
        ];
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
            $channel = new Channel($message);
            $this->client->addChannel($channel);
            $this->logger->debug('Added channel ' . $channel->getName() . ' to client');
            $this->eventHandler->trigger('join', $channel);
            return;
        }
        //TODO: another user joined
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
        if (substr($message->getMessage(), 0, $this->prefixLen) === $this->prefix) {
            $args = explode(' ', $message->getMessage());
            $message->setCommand(substr(array_shift($args), $this->prefixLen));
            $this->eventHandler->trigger('command', $message, $channel, $args);
            return;
        }
        $this->eventHandler->trigger('message', $message, $channel);
    }
}
