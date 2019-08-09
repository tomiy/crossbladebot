<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;

class Component extends Configurable
{

    public function __construct()
    {
        parent::__construct('components/');
    }

    public function register($eventhandler)
    {
        foreach ($this->config->events as $event => $callback) {
            $eventhandler->register($event, [$this, $callback]);
        }

        foreach ($this->config->commands as $command => $callback) {
            $eventhandler->register('command', function ($message, $channel, ...$data) use ($command, $callback) {
                if (!isset($message->command)) return;
                if ($message->command === $command) {
                    $this->$callback($message, $channel, ...$data);
                }
            });
        }
    }

    public function send($data, $channel) {
        $channel->send($data);
    }
}
