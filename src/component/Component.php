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

namespace CrossbladeBot\Component;

use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Command;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Traits\Configurable;
use Exception;
use ReflectionException;

/**
 * The parent component. Holds callbacks to events bound in its config file.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
abstract class Component
{
    use Configurable;

    /**
     * The logger object.
     *
     * @var Logger
     */
    protected Logger $logger;
    /**
     * The client object.
     *
     * @var Client
     */
    protected Client $client;

    protected array $events;
    protected array $commands;

    /**
     * Instantiate a component.
     *
     * @param Logger $logger The logger object.
     * @throws ReflectionException
     */
    public function __construct(Logger $logger)
    {
        $this->loadConfig('components/');
        $this->logger = $logger;

        $this->events = [];
        if (isset($this->_config->events)) {
            foreach ($this->_config->events as $event => $callback) {
                if (method_exists($this, $callback)) {
                    $this->events[$event] = $callback;
                    continue;
                }
                $this->logger->warning('@' . static::class . ' Invalid event callback: ' . $callback);
            }
        }

        $this->commands = [];
        if (isset($this->_config->commands)) {
            foreach ($this->_config->commands as $cmd => $cmdInfo) {
                if (method_exists($this, $cmdInfo->callback)) {
                    $this->commands[$cmd] = new Command($cmd, $cmdInfo, $this);
                    continue;
                }
                $this->logger->warning('@' . static::class . ' Invalid command callback: ' . $cmdInfo->callback);
            }
        }
    }

    /**
     * Registers events using bindings from the config
     *
     * @param EventHandler $eventHandler The event handler.
     * @param Client $client The client object.
     *
     * @return void
     * @throws Exception
     */
    public function register(EventHandler $eventHandler, Client $client): void
    {
        $this->client = $client;

        foreach ($this->events as $event => $callback) {
            $eventHandler->register($event, [$this, $callback]);
        }

        foreach ($this->commands as $cmdObj) {
            $eventHandler->register('command', [$cmdObj, 'execute']);
        }
    }

    /**
     * Send a message to a channel or to the client directly.
     *
     * @param string $message The message to send.
     * @param Channel $channel The channel to send the message.
     *                         If null, the message is sent to the client directly.
     * @param bool $raw Whether it is sent as chat message, or IRC command.
     *
     * @return void
     */
    public function send(
        string $message, Channel $channel = null, $raw = false
    ): void
    {
        if ($channel != null) {
            if ($raw) {
                $channel->sendRaw($message);
                return;
            }
            $channel->send($message);
            return;
        }
        $this->client->send($message);
    }
}
