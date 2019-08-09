<?php

namespace CrossbladeBot\Component\Basic;

use CrossbladeBot\Component\Component;

class ChannelManager extends Component
{
    public function join($message, $channel)
    {
        $channel->sendRaw('JOIN #' . $message->user);
        $channel->send('Joining channel #' . $message->user);
    }

    public function part($message, $channel)
    {
        $channel->sendRaw('PART #' . $message->user);
        $channel->send('Leaving channel #' . $message->user);
    }
}
