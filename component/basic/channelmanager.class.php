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
        if ($channel->name === $this->defaultchannel) {
            $channel->send('Joining channel #' . $message->user);
            $channel->sendRaw('JOIN #' . $message->user);
        }
    }

    public function part($message, $channel)
    {
        if ($channel->name !== $this->defaultchannel) {
            $channel->send('Leaving channel #' . $message->user);
            $channel->sendRaw('PART #' . $message->user);
        }
    }
}
