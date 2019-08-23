<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;
use stdClass;

class Command
{
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

    private $command;
    private $userlevel;
    private $callback;
    private $component;

    public function __construct(string $command, stdClass $params, Component $component)
    {
        $this->command = $command;
        $this->userlevel = static::$USERLEVEL[$params->userlevel];
        $this->callback = $params->callback;
        $this->component = $component;
    }

    public function execute(Message $message, Channel $channel, ...$data): void
    {
        if ($message->getCommand() === $this->command && $channel->getUserLevel($message) >= $this->userlevel) {
            $this->component->{$this->callback}($message, $channel, ...$data);
        }
    }
}
