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

use CrossbladeBot\Service\MessageHandler\AbstractMessageHandler;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

/**
 * Provides function to handle a tmi message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class TmiHandler extends AbstractMessageHandler
{
    /**
     * Initialize the callback map for handling tmi messages.
     *
     * @param Logger       $logger       The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client       $client       The client object.
     */
    public function __construct(
        Logger $logger, EventHandler $eventHandler, Client $client
    ) {
        parent::__construct($logger, $eventHandler, $client);

        $this->callbackMap = [
            '002' => null,
            '003' => null,
            '004' => null,
            '375' => null,
            '376' => null,
            'CAP' => null,
            '001' => 'setClientName',
            '372' => 'notifyConnected',
            'NOTICE' => 'notice',
            'USERNOTICE' => 'userNotice',
            'HOSTTARGET' => null,
            'CLEARCHAT' => null,
            'CLEARMSG' => null,
            'RECONNECT' => null,
            'USERSTATE' => 'userState',
            'GLOBALUSERSTATE' => null,
            'ROOMSTATE' => null,
            'SERVERCHANGE' => null
        ];
    }

    /**
     * Set the bot's name.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function setClientName(Message $message): void
    {
        $this->client->setName($message->getParam(0));
    }

    /**
     * Trigger the event that we are connected to the irc.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function notifyConnected(Message $message): void
    {
        $this->logger->debug('Client connected');
        $this->eventHandler->trigger('connect');
    }
}
