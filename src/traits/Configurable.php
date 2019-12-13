<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Traits;

use stdClass;

/**
 * Loads and parses a json config file to an stdClass object.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
trait Configurable
{
    /**
     * The stdClass object that holds the config from the json file.
     *
     * @var stdClass
     */
    private stdClass $_config;

    /**
     * Load and parses the json file
     *
     * @param string $subFolder (optional) The subfolder to load from.
     *                          If empty, defaults to the root config folder.
     *
     * @return void
     */
    public function loadConfig(string $subFolder = null): void
    {
        $class = strtolower((new \ReflectionClass($this))->getShortName());
        $filePath = getcwd() . '/config/' . $subFolder . $class . '.json';
        $this->_config = json_decode(
            file_get_contents($filePath),
            false,
            512,
            JSON_FORCE_OBJECT
        );
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
