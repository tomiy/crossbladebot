<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Debug\Logger;

class Component
{
    use Configurable;

    protected static $USERLEVEL = [
        'user' => 0,
        'mod' => 1,
        'owner' => 2
    ];

    protected $logger;
    protected $client;

    public function __construct(Logger $logger)
    {
        $this->loadConfig('components/');
        $this->logger = $logger;
    }

    public function register(EventHandler $eventhandler, Client $client): void
    {
        $this->client = $client;

        if (isset($this->config->events)) {
            foreach ($this->config->events as $event => $callback) {
                $eventhandler->register($event, [$this, $callback]);
            }
        }

        if (isset($this->config->commands)) {
            foreach ($this->config->commands as $command => $cmdinfo) {
                $eventhandler->register('command', function ($message, $channel, ...$data) use ($command, $cmdinfo) {
                    if ($message->getCommand() === null) return;

                    if ($message->getCommand() === $command) {
                        if ($channel->getUserLevel($message) < static::$USERLEVEL[$cmdinfo->userlevel]) return false;
                        return $this->{$cmdinfo->callback}($message, $channel, ...$data);
                    }
                });
            }
        }
    }

    public function send(string $data, Channel $channel): string
    {
        return $channel->send($data);
    }
}
