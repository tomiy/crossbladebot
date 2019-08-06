<?php

namespace CrossbladeBot\Component\Impl;

use CrossbladeBot\Component\Component;

class HelloWorld extends Component
{
    public function hello()
    {
        print_r('Hello!' . NL);
    }

    public function test($message)
    {
        print_r('Command: ' . $message->command . NL);
    }
}
