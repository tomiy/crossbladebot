<?php

namespace CrossbladeBot\Chat;

use CrossbladeBot\Traits\RateLimit;

class Whisper extends RateLimit
{
    private $accounts;
    private $accountcap;

    public function __construct()
    {
        $this->accounts = [];
        $this->accountcap = 40;
        parent::__construct(1.6, 1);
    }

    public function send($message)
    {
        $this->limit();
        //send the message
    }
}
