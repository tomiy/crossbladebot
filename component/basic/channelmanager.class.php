<?php

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Component\Component;

class ChannelManager extends Component
{
    public function join($message, $channel)
    {
        if ($channel->name === '#crossbladebot') { //TODO: make it a setting
            $channel->sendRaw('JOIN #' . $message->user);
            $channel->send('Joining channel #' . $message->user);
        }
    }
    
    public function part($message, $channel)
    {
        if ($channel->name !== '#crossbladebot') { //TODO: make it a setting
            $channel->sendRaw('PART #' . $message->user);
            $channel->send('Leaving channel #' . $message->user);
        }
    }
}
