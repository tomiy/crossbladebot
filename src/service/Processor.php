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

namespace CrossbladeBot\Service;

use CrossbladeBot\Service\MessageHandler\UserMessageHandler;
use CrossbladeBot\Service\MessageHandler\TmiHandler;
use CrossbladeBot\Service\MessageHandler\PingHandler;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

/**
 * Provides function to process IRC messages for the client.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Processor
{
    /**
     * The logger object.
     *
     * @var Logger
     */
    private $_logger;
    /**
     * The event handler holding the component events.
     *
     * @var EventHandler;
     */
    private $_eventHandler;
    /**
     * The client object.
     *
     * @var Client
     */
    private $_client;

    /**
     * The command prefix.
     *
     * @var string
     */
    private $_prefix;
    /**
     * The length of the prefix.
     *
     * @var int
     */
    private $_prefixLen;

    /**
     * The message handler for pings.
     *
     * @var PingHandler
     */
    private $_pingHandler;
    /**
     * The message handler for user messages.
     *
     * @var UserMessageHandler
     */
    private $_userMessageHandler;
    /**
     * The message handler for tmi messages.
     *
     * @var TmiHandler
     */
    private $_tmiHandler;

    /**
     * Instantiate a new processor.
     *
     * @param Logger       $logger       The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client       $client       The client object.
     */
    public function __construct(
        Logger $logger,
        EventHandler $eventHandler,
        Client $client
    ) {
        $this->_logger = $logger;
        $this->_eventHandler = $eventHandler;
        $this->_client = $client;

        $this->_prefix = $client->getConfig()->prefix;
        $this->_prefixLen = strlen($this->_prefix);

        $this->_pingHandler = new PingHandler($logger, $eventHandler, $client);
        $this->_userMessageHandler = new UserMessageHandler(
            $logger, $eventHandler, $client
        );
        $this->_tmiHandler = new TmiHandler($logger, $eventHandler, $client);
    }

    /**
     * Handle a message.
     *
     * @param Message $message the message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        switch ($message->getFrom()) {
        case null:
        case '':
            $this->_handlePing($message);
            break;
        case 'tmi.twitch.tv':
            $this->_handleTmi($message);
            break;
        case 'jtv':
            $this->_handleJtv($message);
            break;
        default:
            $this->_handleUserMessage($message);
            break;
        }
    }

    /**
     * Handle ping messages (coming directly from the stream)
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _handlePing(Message $message): void
    {
        $this->_pingHandler->handle($message);
    }

    /**
     * Handle messages coming from tmi.twitch.tv.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _handleTmi(Message $message): void
    {
        $this->_tmiHandler->handle($message);
    }

    /**
     * Handle messages coming from jtv.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _handleJtv(Message $message): void
    {
        switch ($message->getType()) {
        case 'MODE':
            break;
        default:
            $this->_cantParse($message);
            break;
        }
    }

    /**
     * Handle messages coming from users (<user>!<user>@<user>.tmi.twitch.tv)
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _handleUserMessage(Message $message): void
    {
        $this->_userMessageHandler->handle($message);
    }
}
