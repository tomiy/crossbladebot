<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;

class HelloWorld extends Component
{
    public function evtjoin(Channel $channel): void
    {
        $connectmsg = 'Connected to channel ' . $channel->getName();
        $this->logger->debug($connectmsg);
        $this->send($connectmsg, $channel);
    }

    public function cmdtest(Message $message, Channel $channel): void
    {
        $this->send('Command: ' . $message->getCommand(), $channel);
    }
}
