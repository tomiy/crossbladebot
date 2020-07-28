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

namespace crossbladebot\basic;

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
class Configuration
{
    /**
     * The base config folder path.
     * 
     * @var string
     */
    private static string $_baseFolder;
    /**
     * The stdClass object that holds the config from the json file.
     *
     * @var stdClass
     */
    private stdClass $_config;

    /**
     * Load and parses the json file.
     *
     * @param string $path the path to the json config.
     *
     * @return void
     */
    public static function load(string $path): self
    {
        return new self(
            json_decode(
                file_get_contents(rtrim(self::getBaseFolder(), '/') . '/' . ltrim($path, '/')),
                false,
                512,
                JSON_FORCE_OBJECT
            )
        );
    }
    
    private function __construct(stdClass $config)
    {
        $this->setConfig($config);
    }

    /**
     * Get a variable from the config.
     *
     * @param string $key the variable name.
     *
     * @return mixed the variable.
     */
    public function get(string $key)
    {
        if(isset($this->getConfig()->{$key})) {
            return $this->getConfig()->{$key};
        }
        
        return null;
    }
    
    /**
     * @return stdClass
     */
    public function getConfig(): stdClass
    {
        return $this->_config;
    }
    
    public function setConfig(stdClass $config): void
    {
        $this->_config = $config;
    }
    /**
     * @return string
     */
    public static function getBaseFolder()
    {
        return self::$_baseFolder;
    }

    /**
     * @param string $_baseFolder
     */
    public static function setBaseFolder($_baseFolder)
    {
        self::$_baseFolder = $_baseFolder;
    }

}
