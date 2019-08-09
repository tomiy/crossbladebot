<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;

class Component extends Configurable
{
    protected $logger;
    
    public function __construct($logger)
    {
        parent::__construct('components/');
        $this->logger = $logger;
    }

    public function register($eventhandler)
    {
        if(isset($this->config->events)) {
            foreach ($this->config->events as $event => $callback) {
                $eventhandler->register($event, [$this, $callback]);
            }
        }

        if(isset($this->config->commands)) {
            foreach ($this->config->commands as $command => $callback) {
                $eventhandler->register('command', function ($message, $channel, ...$data) use ($command, $callback) {
                    if (!isset($message->command)) return;
                    if ($message->command === $command) {
                        $this->$callback($message, $channel, ...$data);
                    }
                });
            }
        }
    }

    public function send($data, $channel) {
        $channel->send($data);
    }
}
