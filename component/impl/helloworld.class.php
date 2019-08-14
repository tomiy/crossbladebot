<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;

class HelloWorld extends Component
{
    public function join(Channel $channel): array
    {
        $connectmsg = 'Connected to channel ' . $channel->getName();
        $this->logger->info($connectmsg);
        return [$this->send($connectmsg, $channel)];
    }

    public function command(Message $message, Channel $channel): array
    {
        return [$this->send('Command: ' . $message->getCommand(), $channel)];
    }
}
