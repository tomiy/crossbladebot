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
 * Provides function to handle a type of message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
abstract class AbstractMessageHandler
{
    /**
     * The logger object.
     *
     * @var Logger
     */
    protected Logger $logger;
    /**
     * The event handler holding the component events.
     *
     * @var EventHandler;
     */
    protected EventHandler $eventHandler;
    /**
     * The client object.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * The command prefix.
     *
     * @var string
     */
    protected string $prefix;
    /**
     * The length of the prefix.
     *
     * @var int
     */
    protected int $prefixLen;

    /**
     * A map of types and callbacks.
     *
     * @var array
     */
    protected array $callbackMap;

    /**
     * Instantiate a new message handler.
     *
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(EventHandler $eventHandler, Client $client)
    {
        $this->logger = Logger::getInstance();
        $this->eventHandler = $eventHandler;
        $this->client = $client;

        $this->prefix = $client->getConfig('prefix');
        $this->prefixLen = strlen($this->prefix);
    }

    /**
     * Handle a message.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handle(Message $message): void
    {
        if (array_key_exists($message->getType(), $this->callbackMap)) {
            $method = $this->callbackMap[$message->getType()];
            if (!empty($method) && method_exists($this, $method)) {
                $this->{$method}($message);
            }
            return;
        }
        $this->cantParse($message);
    }

    /**
     * Log the message that can't be parsed at warning level.
     *
     * @param Message $message The message to log.
     *
     * @return void
     */
    protected function cantParse(Message $message): void
    {
        $this->logger->warning('Could not parse message: ' . $message->getRaw());
    }
}
