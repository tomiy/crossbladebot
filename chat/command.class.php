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
    private static $_USERLEVEL = [
        'user' => 0,
        'mod' => 1,
        'owner' => 2
    ];

    private $_command;
    private $_userLevel;
    private $_callback;
    private $_component;

    public function __construct(string $command, stdClass $params, Component $component)
    {
        $this->_command = $command;
        $this->_userLevel = static::$_USERLEVEL[$params->userLevel];
        $this->_callback = $params->callback;
        $this->_component = $component;
    }

    public function execute(Message $message, Channel $channel, ...$data): void
    {
        if ($message->getCommand() === $this->_command && $channel->getUserLevel($message) >= $this->_userLevel) {
            $this->_component->{$this->_callback}($message, $channel, ...$data);
        }
    }
}
