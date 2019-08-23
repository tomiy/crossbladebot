<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Debug\Logger;

/**
 * The parent component. Holds callbacks to events bound in its config file.
 */
class Component
{
    use Configurable;

    /**
     * Defines the corresponding index of user level strings.
     *
     * @var array
     */
    protected static $USERLEVEL = [
        'user' => 0,
        'mod' => 1,
        'owner' => 2
    ];

    /**
     * The logger object.
     *
     * @var Logger
     */
    protected $logger;
    /**
     * The client object.
     *
     * @var Client
     */
    protected $client;

    public function __construct(Logger $logger)
    {
        $this->loadConfig('components/');
        $this->logger = $logger;
    }

    /**
     * Registers events using bindings from the config
     *
     * @param EventHandler $eventhandler The event handler.
     * @param Client $client The client object.
     * @return void
     */
    public function register(EventHandler $eventhandler, Client $client): void
    {
        $this->client = $client;

        if (isset($this->config->events)) {
            foreach ($this->config->events as $event => $callback) {
                if (method_exists($this, $callback)) {
                    $eventhandler->register($event, [$this, $callback]);
                } else {
                    $this->logger->warning(
                        '@' . get_class(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['object']) .
                        ' Invalid event callback: ' . $callback
                    );
                }
            }
        }

        if (isset($this->config->commands)) {
            foreach ($this->config->commands as $command => $cmdinfo) {
                if (method_exists($this, $cmdinfo->callback)) {
                    $eventhandler->register('command', function (Message $message, Channel $channel, ...$data) use ($command, $cmdinfo) {
                        if ($message->getCommand() === null || $channel->getUserLevel($message) < static::$USERLEVEL[$cmdinfo->userlevel]) {
                            return;
                        }

                        if ($message->getCommand() === $command) {
                            $this->{$cmdinfo->callback}($message, $channel, ...$data);
                        }
                    });
                } else {
                    $this->logger->warning(
                        '@' . get_class(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['object']) .
                        ' Invalid event callback: ' . $cmdinfo->callback
                    );
                }
            }
        }
    }

    /**
     * Send a message to a channel or to the client directly.
     *
     * @param string $message The message to send.
     * @param Channel $channel The channel to send the message. If null, the message is sent to the client directly.
     * @param boolean $raw Whether the message is sent as a chat message, or an IRC command.
     * @return void
     */
    public function send(string $message, Channel $channel = null, $raw = false): void
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
