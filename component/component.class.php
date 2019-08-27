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
    protected $_logger;
    /**
     * The client object.
     *
     * @var Client
     */
    protected $_client;

    protected $_events;
    protected $_commands;

    public function __construct(Logger $logger)
    {
        $this->loadConfig('components/');
        $this->_logger = $logger;

        $this->_events = [];
        if (isset($this->_config->events)) {
            foreach ($this->_config->events as $event => $callback) {
                if (method_exists($this, $callback)) {
                    $this->_events[$event] = $callback;
                } else {
                    $this->_logger->warning(
                        '@' . get_class(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['object']) .
                        ' Invalid event callback: ' . $callback
                    );
                }
            }
        }

        $this->_commands = [];
        if (isset($this->_config->commands)) {
            foreach ($this->_config->commands as $command => $cmdInfo) {
                if (method_exists($this, $cmdInfo->callback)) {
                    $this->_commands[$command] = new Command($command, $cmdInfo, $this);
                } else {
                    $this->_logger->warning(
                        '@' . get_class(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['object']) .
                            ' Invalid command callback: ' . $cmdInfo->callback
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
    public function register(EventHandler $eventHandler, Client $client): void
    {
        $this->_client = $client;

        foreach ($this->_events as $event => $callback) {
            $eventHandler->register($event, [$this, $callback]);
        }

        foreach ($this->_commands as $command => $cmdObj) {
            $eventHandler->register('command', [$cmdObj, 'execute']);
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
        $this->_client->send($message);
    }
}
