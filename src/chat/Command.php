<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Chat;

use stdClass;
use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

/**
 * Provides an extensible object to hold commands and callbacks.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Command
{
    /**
     * Defines the corresponding index of user level strings.
     *
     * @var array
     */
    const USERLEVEL = [
        'user' => 0,
        'mod' => 1,
        'owner' => 2
    ];

    private $_command;
    private $_userLevel;
    private $_callback;
    private $_component;

    /**
     * Instantiate a command.
     *
     * @param string    $cmd       The command name.
     * @param stdClass  $params    The config object that hold the command params.
     * @param Component $component The component to bind the command to.
     */
    public function __construct(string $cmd, stdClass $params, Component $component)
    {
        $this->_command = $cmd;
        $this->_userLevel = static::USERLEVEL[$params->userLevel];
        $this->_callback = $params->callback;
        $this->_component = $component;
    }

    /**
     * Checks if the command is called and if the user can perform it
     *
     * @param Message $message The message that calls a command.
     * @param Channel $channel The channel the message is from.
     * @param mixed   ...$data Additional data that can be passed to the command
     *
     * @return void
     */
    public function execute(Message $message, Channel $channel, ...$data): void
    {
        if ($message->getCommand() === $this->_command
            && $channel->getUserLevel($message) >= $this->_userLevel
        ) {
            $this->_component->{$this->_callback}($message, $channel, ...$data);
        }
    }
}
