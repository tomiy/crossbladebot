<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;

class HelloWorld extends Component
{
    public function join($channel)
    {
        $connectmsg = 'Connected to channel ' . $channel->name . NL;
        $this->send($connectmsg, $channel);
        sleep(3);
        print_r($connectmsg);
        $this->logger->info($connectmsg);
    }

    public function command($message, $channel)
    {
        $this->send('Command: ' . $message->command . NL, $channel);
    }
}
