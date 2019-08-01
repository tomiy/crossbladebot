<?php

namespace CrossbladeBot\Traits;

abstract class Configurable
{
    protected $config;

    public function __construct($subfolder = null)
    {
        $class = strtolower((new \ReflectionClass($this))->getShortName());
        $filepath = getcwd() . '/config/' . $subfolder . $class . '.json';
        $this->config = json_decode(file_get_contents($filepath), false, 512, JSON_FORCE_OBJECT);
    }
}
