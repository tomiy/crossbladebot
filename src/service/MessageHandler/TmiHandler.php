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
     * Handle tmi messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        switch ($message->getType()) {
        case '002':
        case '003':
        case '004':
        case '375':
        case '376':
        case 'CAP':
            break;
        case '001':
                $this->client->setName($message->getParam(0));
            break;
        case '372':
                $this->logger->debug('Client connected');
                $this->eventHandler->trigger('connect');
            break;
        case 'NOTICE':
            $this->notice($message);
            break;
        case 'USERNOTICE':
            $this->userNotice($message);
            break;
        case 'HOSTTARGET':
            break;
        case 'CLEARCHAT':
            break;
        case 'CLEARMSG':
            break;
        case 'RECONNECT':
            break;
        case 'USERSTATE':
            $this->userState($message);
            break;
        case 'GLOBALUSERSTATE':
            break;
        case 'ROOMSTATE':
            break;
        case 'SERVERCHANGE':
            break;
        default:
            $this->cantParse($message);
            break;
        }
    }
}
