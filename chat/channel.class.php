<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;

class Channel extends RateLimit
{
    private $name;

    public function __construct(Message $join)
    {
        if ($join->isMod) {
            parent::__construct(100, 30);
        } else {
            parent::__construct(20, 30);
        }
    }

    public function send($message)
    {
        $this->limit();
        //send the message
    }
}
