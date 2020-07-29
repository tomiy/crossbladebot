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

namespace crossbladebot\component;

use crossbladebot\core\Client;
use crossbladebot\core\EventHandler;
use crossbladebot\basic\Collection;

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
     * @var Collection
     */
    private Collection $_components;

    /**
     * Instantiate the loader and all its components.
     *
     */
    public function __construct()
    {
        $this->setComponents(new Collection());
        
        foreach (glob(__DIR__ . '/basic/*.php') as $file) {
            $name = basename($file, '.php');
            $class = __NAMESPACE__ . '\basic\\' . ucfirst($name);
            $this->getComponents()->set($name, new $class());
        }

        foreach (glob(__DIR__ . '/impl/*.php') as $file) {
            $name = basename($file, '.php');
            $class = __NAMESPACE__ . '\impl\\' . ucfirst($name);
            $this->getComponents()->set($name, new $class());
        }
    }

    /**
     * Registers every component's events
     *
     * @param EventHandler $eventHandler The event handler to register into.
     * @param Client $client The bot client, used in the components.
     *
     * @return void
     */
    public function register(EventHandler $eventHandler, Client $client): void
    {
        foreach ($this->getComponents() as $component) {
            $component->register($eventHandler, $client);
        }
    }

    /**
     * Get the list of components.
     *
     * @return array
     */
    public function getComponents(): Collection
    {
        return $this->_components;
    }
    /**
     * @param array $_components
     */
    public function setComponents(Collection $_components)
    {
        $this->_components = $_components;
    }

}
