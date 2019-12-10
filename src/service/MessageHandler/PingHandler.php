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
     * Handle ping messages (coming directly from the stream)
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        switch ($message->getType()) {
        case 'PING':
                $this->client->setLastPing(time());
                $this->client->send('PONG :' . $message->getParam(0));
                $this->eventHandler->trigger('pong');
            break;
        case 'PONG':
                $latency = time() - $this->client->getLastPing();
                $this->logger->info('Current latency: ' . $latency);
            break;
        default:
                $this->cantParse($message);
            break;
        }
    }
}
