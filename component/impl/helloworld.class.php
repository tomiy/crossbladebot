<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;

class HelloWorld extends Component
{
    public function hello()
    {
        print_r('Hello!' . NL);
    }

    public function test($message, $channel)
    {
        $output = 'Command: ' . $message->command . NL;
        print_r($output);
        $this->send($output, $channel);
    }
}
