<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;

class HelloWorld extends Component
{
    public function join($channel)
    {
        $connectmsg = 'Connected to channel ' . $channel->getName();
        $this->send($connectmsg, $channel);
        print_r($connectmsg);
        $this->logger->info($connectmsg);
        sleep(3);
    }

    public function command($message, $channel)
    {
        $this->send('Command: ' . $message->getCommand(), $channel);
    }
}
