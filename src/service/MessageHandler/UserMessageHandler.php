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
     * Handle user messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        switch ($message->getType()) {
        case '353':
            break;
        case '366':
            break;
        case 'JOIN':
            $this->join($message);
            break;
        case 'PART':
            $this->part($message);
            break;
        case 'WHISPER':
            break;
        case 'PRIVMSG':
            $this->privMsg($message);
            break;
        default:
            $this->cantParse($message);
            break;
        }
    }
}
