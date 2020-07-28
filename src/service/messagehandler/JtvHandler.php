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

/**
 * Provides function to handle a jtv message. (absolutely useless but required)
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class JtvHandler extends AbstractMessageHandler
{
    /**
     * Initialize the callback map for handling ping messages.
     *
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(EventHandler $eventHandler, Client $client)
    {
        parent::__construct($eventHandler, $client);

        $this->callbackMap = [
            'MODE' => null
        ];
    }
}
