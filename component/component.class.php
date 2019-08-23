<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Command;
use CrossbladeBot\Debug\Logger;

/**
 * The parent component. Holds callbacks to events bound in its config file.
 */
class Component
{
    use Configurable;

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

    protected $events;
    protected $commands;

    public function __construct(Logger $logger)
    {
        $this->loadConfig('components/');
        $this->logger = $logger;

        $this->events = [];
        if (isset($this->config->events)) {
            foreach ($this->config->events as $event => $callback) {
                if (method_exists($this, $callback)) {
                    $this->events[$event] = $callback;
                } else {
                    $this->logger->warning(
                        '@' . get_class(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['object']) .
                        ' Invalid event callback: ' . $callback
                    );
                }
            }
        }

        $this->commands = [];
        if (isset($this->config->commands)) {
            foreach ($this->config->commands as $command => $cmdinfo) {
                if (method_exists($this, $cmdinfo->callback)) {
                    $this->commands[$command] = new Command($command, $cmdinfo, $this);
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
     * Registers events using bindings from the config
     *
     * @param EventHandler $eventhandler The event handler.
     * @param Client $client The client object.
     * @return void
     */
    public function register(EventHandler $eventhandler, Client $client): void
    {
        $this->client = $client;

        foreach ($this->events as $event => $callback) {
            $eventhandler->register($event, [$this, $callback]);
        }

        foreach ($this->commands as $command => $cmdobj) {
            $eventhandler->register('command', function (Message $message, Channel $channel, ...$data) use($cmdobj) {
                $cmdobj->execute($message, $channel, ...$data);
            });
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
