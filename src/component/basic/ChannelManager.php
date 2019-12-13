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

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

/**
 * Component responsible for joining and parting channels.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class ChannelManager extends Component
{
    private string $_defaultChannel;

    /**
     * (Override Component::register) register events to the handler.
     *
     * @param EventHandler $eventHandler The event handler to register in.
     * @param Client       $client       The client object.
     *
     * @return void
     */
    public function register(EventHandler $eventHandler, Client $client): void
    {
        parent::register($eventHandler, $client);
        $this->_defaultChannel = '#' . $client->getConfig()->channel;
    }

    /**
     * Join a channel.
     *
     * @param Message $message The message requesting to join.
     * @param Channel $channel The channel the message is from.
     *
     * @return void
     */
    public function cmdJoin(Message $message, Channel $channel): void
    {
        if ($channel->getName() === $this->_defaultChannel) {
            $this->send('Joining channel #' . $message->getUser(), $channel);
            $this->send('JOIN #' . $message->getUser());
        }
    }

    /**
     * Part a channel.
     *
     * @param Message $message The message requesting to part.
     * @param Channel $channel The channel the message is from.
     *
     * @return void
     */
    public function cmdPart(Message $message, Channel $channel): void
    {
        if ($channel->getName() !== $this->_defaultChannel) {
            $this->send('Leaving channel #' . $message->getUser(), $channel);
            $this->send('PART #' . $message->getUser());
        }
    }
}
