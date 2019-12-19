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

namespace crossbladebot\service;

use crossbladebot\chat\Message;
use crossbladebot\core\Client;
use crossbladebot\core\EventHandler;
use crossbladebot\debug\Logger;
use crossbladebot\service\messagehandler\JtvHandler;
use crossbladebot\service\messagehandler\PingHandler;
use crossbladebot\service\messagehandler\TmiHandler;
use crossbladebot\service\messagehandler\UserMessageHandler;

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
     * The message handler for pings.
     *
     * @var PingHandler
     */
    private PingHandler $_pingHandler;
    /**
     * The message handler for user messages.
     *
     * @var UserMessageHandler
     */
    private UserMessageHandler $_userMessageHandler;
    /**
     * The message handler for tmi messages.
     *
     * @var TmiHandler
     */
    private TmiHandler $_tmiHandler;
    /**
     * The message handler for jtv messages.
     *
     * @var JtvHandler
     */
    private JtvHandler $_jtvHandler;

    /**
     * Instantiate a new processor.
     *
     * @param Logger $logger The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(Logger $logger, EventHandler $eventHandler, Client $client)
    {
        $this->_pingHandler = new PingHandler($logger, $eventHandler, $client);
        $this->_tmiHandler = new TmiHandler($logger, $eventHandler, $client);
        $this->_jtvHandler = new JtvHandler($logger, $eventHandler, $client);
        $this->_userMessageHandler = new UserMessageHandler(
            $logger, $eventHandler, $client
        );
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
                $this->_pingHandler->handle($message);
                break;
            case 'tmi.twitch.tv':
                $this->_tmiHandler->handle($message);
                break;
            case 'jtv':
                $this->_jtvHandler->handle($message);
                break;
            default:
                $this->_userMessageHandler->handle($message);
                break;
        }
    }
}
