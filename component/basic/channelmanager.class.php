<?php

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

class ChannelManager extends Component
{
    private $_defaultChannel;

    public function register(EventHandler $eventHandler, Client $client): void
    {
        parent::register($eventHandler, $client);
        $this->_defaultChannel = '#' . $client->getConfig()->channel;
    }

    public function cmdJoin(Message $message, Channel $channel): void
    {
        if ($channel->getName() === $this->_defaultChannel) {
            $this->send('Joining channel #' . $message->getUser(), $channel);
            $this->send('JOIN #' . $message->getUser());
        }
    }

    public function cmdPart(Message $message, Channel $channel): void
    {
        if ($channel->getName() !== $this->_defaultChannel) {
            $this->send('Leaving channel #' . $message->getUser(), $channel);
            $this->send('PART #' . $message->getUser());
        }
    }
}
