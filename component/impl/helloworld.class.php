<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;

class HelloWorld extends Component
{
    public function join(Channel $channel): void
    {
        $connectmsg = 'Connected to channel ' . $channel->getName();
        $this->send($connectmsg, $channel);
        print_r($connectmsg . NL);
        $this->logger->info($connectmsg);
        sleep(3);
    }

    public function command(Message $message, Channel $channel): void
    {
        $this->send('Command: ' . $message->getCommand(), $channel);
    }
}
