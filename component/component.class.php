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

    public function register($eventhandler)
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
                    //check user level here and compare to command level (to add in json)
                    if ($message->command === $command) {
                        print_r($channel->getUserLevel($message) . NL);
                        print_r(static::$USERLEVEL[$cmdinfo->userlevel] . NL);
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
