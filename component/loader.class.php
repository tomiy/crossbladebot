<?php

namespace CrossbladeBot\Component;

class Loader
{

    private $components;

    public function __construct()
    {
        //iterate through the impl folder and instanciate every class
        foreach (glob('./component/impl/*.class.php') as $file) {
            $name = basename($file, '.class.php');
            $class = __NAMESPACE__ . '\Impl\\' . ucfirst($name);
            $this->components[$name] = new $class();
        }
    }

    public function register($eventhandler)
    {
        foreach ($this->components as $component) {
            $component->register($eventhandler);
        }
    }
}
