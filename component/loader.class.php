<?php

namespace CrossbladeBot\Component;

class Loader
{

    private $components;

    public function __construct($logger)
    {
        foreach (glob('./component/basic/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Basic\\' . ucfirst($name);
            $this->components[$name] = new $class($logger);
        }
        //iterate through the impl folder and instanciate every class
        foreach (glob('./component/impl/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Impl\\' . ucfirst($name);
            $this->components[$name] = new $class($logger);
        }
    }

    public function register($eventhandler, $client)
    {
        foreach ($this->components as $component) {
            $component->register($eventhandler, $client);
        }
    }

    public function getComponents()
    {
        return $this->components;
    }
}
