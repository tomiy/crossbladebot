<?php

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

class ChannelManager extends Component
{
    private $defaultchannel;

    public function register(EventHandler $eventhandler, Client $client): void
    {
        parent::register($eventhandler, $client);
        $this->defaultchannel = '#' . $client->getConfig()->channel;
    }

    public function join(Message $message, Channel $channel): array
    {
        if ($channel->getName() === $this->defaultchannel) {
            return [
                $channel->send('Joining channel #' . $message->getUser()),
                $channel->sendRaw('JOIN #' . $message->getUser())
            ];
        }
    }

    public function part(Message $message, Channel $channel): array
    {
        if ($channel->getName() !== $this->defaultchannel) {
            return [
                $channel->send('Leaving channel #' . $message->getUser()),
                $channel->sendRaw('PART #' . $message->getUser())
            ];
        }
    }
}
