<?php

namespace CrossbladeBot\Traits;

use stdClass;

/**
 * Loads and parses a json config file to an stdClass object.
 */
trait Configurable
{
    /**
     * The stdClass object that holds the config from the json file.
     *
     * @var stdClass
     */
    private $config;

    /**
     * Load and parses the json file
     *
     * @param string $subfolder (optional) The subfolder to load from.
     * If empty, defaults to the config folder at the root of the project.
     * @return void
     */
    public function loadConfig(string $subfolder = null): void
    {
        $class = strtolower((new \ReflectionClass($this))->getShortName());
        $filepath = getcwd() . '/config/' . $subfolder . $class . '.json';
        $this->config = json_decode(file_get_contents($filepath), false, 512, JSON_FORCE_OBJECT);
    }

    /**
     * Get the config object.
     *
     * @return stdClass The config object.
     */
    public function getConfig(): stdClass
    {
        return $this->config;
    }
}
