<?php

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Component\Component;

class ChannelManager extends Component
{
    private $defaultchannel;

    public function register($eventhandler, $client)
    {
        parent::register($eventhandler, $client);
        $this->defaultchannel = '#' . $client->getConfig()->channel;
    }

    public function join($message, $channel)
    {
        if ($channel->getName() === $this->defaultchannel) {
            $channel->send('Joining channel #' . $message->getUser());
            $channel->sendRaw('JOIN #' . $message->getUser());
        }
    }

    public function part($message, $channel)
    {
        if ($channel->getName() !== $this->defaultchannel) {
            $channel->send('Leaving channel #' . $message->getUser());
            $channel->sendRaw('PART #' . $message->getUser());
        }
    }
}
