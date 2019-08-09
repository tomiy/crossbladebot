<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;

class HelloWorld extends Component
{
    public function join($channel)
    {
        $this->send('Connected to channel ' . $channel->name . NL, $channel);
        sleep(3);
    }

    public function command($message, $channel)
    {
        $this->send('Command: ' . $message->command . NL, $channel);
    }
}
