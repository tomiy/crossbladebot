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
    private $_config;

    /**
     * Load and parses the json file
     *
     * @param string $subfolder (optional) The subfolder to load from.
     * If empty, defaults to the config folder at the root of the project.
     * @return void
     */
    public function loadConfig(string $subFolder = null): void
    {
        $class = strtolower((new \ReflectionClass($this))->getShortName());
        $filePath = getcwd() . '/config/' . $subFolder . $class . '.json';
        $this->_config = json_decode(file_get_contents($filePath), false, 512, JSON_FORCE_OBJECT);
    }

    /**
     * Get the config object.
     *
     * @return stdClass The config object.
     */
    public function getConfig(): stdClass
    {
        return $this->_config;
    }
}
