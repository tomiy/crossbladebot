<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Chat\Message;

class Whisper
{
    use RateLimit;

    private $accounts;
    private $accountcap;

    public function __construct()
    {
        $this->accounts = [];
        $this->accountcap = 40;
        $this->initRate(1.6, 1);
    }

    public function send(Message $message): void
    {
        $this->limit();
        //send the message
    }
}
