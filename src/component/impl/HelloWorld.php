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

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;

/**
 * Example component to demonstrate events and commands.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class HelloWorld extends Component
{
    /**
     * Messages the channel when it joins it.
     *
     * @param Channel $channel The channel joined.
     *
     * @return void
     */
    public function evtJoin(Channel $channel): void
    {
        $connectMsg = 'Connected to channel ' . $channel->getName();
        $this->logger->debug($connectMsg);
        $this->send($connectMsg, $channel);
    }

    /**
     * Send back the name of the command triggered.
     *
     * @param Message $message The message triggering the command.
     * @param Channel $channel The channel the message is from.
     *
     * @return void
     */
    public function cmdTest(Message $message, Channel $channel): void
    {
        $this->send('Command: ' . $message->getCommand(), $channel);
    }
}
