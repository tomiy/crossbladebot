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
     * @param Logger $logger The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(Logger $logger, EventHandler $eventHandler, Client $client)
    {
        parent::__construct($logger, $eventHandler, $client);

        $this->callbackMap = [
            '353' => null,
            '366' => null,
            'JOIN' => 'join',
            'PART' => 'part',
            'WHISPER' => null,
            'PRIVMSG' => 'privMsg'
        ];
    }
}
