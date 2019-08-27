<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Chat\Message;

class HelloWorld extends Component
{
    public function evtJoin(Channel $channel): void
    {
        $connectMsg = 'Connected to channel ' . $channel->getName();
        $this->_logger->debug($connectMsg);
        $this->send($connectMsg, $channel);
    }

    public function cmdTest(Message $message, Channel $channel): void
    {
        $this->send('Command: ' . $message->getCommand(), $channel);
    }
}
