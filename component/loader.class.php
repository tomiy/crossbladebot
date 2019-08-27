<?php

namespace CrossbladeBot\Component;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;

/**
 * Dynamically loads every class from the component folders.
 */
class Loader
{

    /**
     * The array of loaded components.
     *
     * @var array
     */
    private $_components;

    public function __construct(Logger $logger)
    {
        foreach (glob('./component/basic/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Basic\\' . ucfirst($name);
            $this->_components[$name] = new $class($logger);
        }

        foreach (glob('./component/impl/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Impl\\' . ucfirst($name);
            $this->_components[$name] = new $class($logger);
        }
    }

    /**
     * Registers every component's events
     *
     * @param EventHandler $eventhandler The event handler to register into.
     * @param Client $client The bot client, used to get referenced in the components.
     * @return void
     */
    public function register(EventHandler $eventHandler, Client $client): void
    {
        foreach ($this->_components as $component) {
            $component->register($eventHandler, $client);
        }
    }

    public function getComponents(): array
    {
        return $this->_components;
    }
}
