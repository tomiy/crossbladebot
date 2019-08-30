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

namespace CrossbladeBot\Component;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;

/**
 * Dynamically loads every class from the component folders.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Loader
{

    /**
     * The array of loaded components.
     *
     * @var array
     */
    private $_components;

    /**
     * Instantiate the loader and all its components.
     *
     * @param Logger $logger The logger object.
     */
    public function __construct(Logger $logger)
    {
        foreach (glob(__DIR__ . '/basic/*.php') as $file) {
            $name = basename($file, '.php');
            $class = __NAMESPACE__ . '\Basic\\' . ucfirst($name);
            $this->_components[$name] = new $class($logger);
        }

        foreach (glob(__DIR__ . '/impl/*.php') as $file) {
            $name = basename($file, '.php');
            $class = __NAMESPACE__ . '\Impl\\' . ucfirst($name);
            $this->_components[$name] = new $class($logger);
        }
    }

    /**
     * Registers every component's events
     *
     * @param EventHandler $eventHandler The event handler to register into.
     * @param Client       $client       The bot client, used in the components.
     *
     * @return void
     */
    public function register(EventHandler $eventHandler, Client $client): void
    {
        foreach ($this->_components as $component) {
            $component->register($eventHandler, $client);
        }
    }

    /**
     * Get the list of components.
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->_components;
    }
}
