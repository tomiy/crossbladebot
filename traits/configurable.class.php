<?php

namespace CrossbladeBot\Traits;

use stdClass;

trait Configurable
{
    protected $config;

    public function loadConfig(string $subfolder = null)
    {
        $class = strtolower((new \ReflectionClass($this))->getShortName());
        $filepath = getcwd() . '/config/' . $subfolder . $class . '.json';
        $this->config = json_decode(file_get_contents($filepath), false, 512, JSON_FORCE_OBJECT);
    }

    public function getConfig(): stdClass
    {
        return $this->config;
    }
}
