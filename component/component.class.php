<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;

class Component extends Configurable
{
    protected static $USERLEVEL = [
        'user' => 0,
        'mod' => 1,
        'owner' => 2
    ];

    protected $logger;

    public function __construct($logger)
    {
        parent::__construct('components/');
        $this->logger = $logger;
    }

    public function register($eventhandler, $client)
    {
        if(isset($this->config->events)) {
            foreach ($this->config->events as $event => $callback) {
                $eventhandler->register($event, [$this, $callback]);
            }
        }

        if(isset($this->config->commands)) {
            foreach ($this->config->commands as $command => $cmdinfo) {
                $eventhandler->register('command', function ($message, $channel, ...$data) use ($command, $cmdinfo) {
                    if (!isset($message->command)) return;

                    if ($message->command === $command) {
                        if ($channel->getUserLevel($message) < static::$USERLEVEL[$cmdinfo->userlevel]) return;
                        $this->{$cmdinfo->callback}($message, $channel, ...$data);
                    }
                });
            }
        }
    }

    public function send($data, $channel) {
        $channel->send($data);
    }
}
