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

use crossbladebot\chat\Message;
use crossbladebot\core\Client;
use crossbladebot\core\EventHandler;
use crossbladebot\debug\Logger;

/**
 * Provides function to handle a ping message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class PingHandler extends AbstractMessageHandler
{
    /**
     * Initialize the callback map for handling ping messages.
     *
     * @param Logger $logger The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(Logger $logger, EventHandler $eventHandler, Client $client)
    {
        parent::__construct($logger, $eventHandler, $client);

        $this->callbackMap = [
            'PING' => 'handlePing',
            'PONG' => 'handlePong'
        ];
    }

    /**
     * Handle a ping message.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function handlePing(Message $message): void
    {
        $this->client->setLastPing(time());
        $this->client->send('PONG :' . $message->getParam(0));
        $this->eventHandler->trigger('pong');
    }

    /**
     * Handle a pong event.
     *
     * @return void
     */
    protected function handlePong(): void
    {
        $latency = time() - $this->client->getLastPing();
        $this->logger->info('Current latency: ' . $latency);
    }
}
