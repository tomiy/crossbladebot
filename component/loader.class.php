<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;

class Loader
{

    private $components;

    public function __construct(Logger $logger)
    {
        foreach (glob('./component/basic/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Basic\\' . ucfirst($name);
            $this->components[$name] = new $class($logger);
        }

        foreach (glob('./component/impl/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Impl\\' . ucfirst($name);
            $this->components[$name] = new $class($logger);
        }
    }

    public function register(EventHandler $eventhandler, Client $client): void
    {
        foreach ($this->components as $component) {
            $component->register($eventhandler, $client);
        }
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
