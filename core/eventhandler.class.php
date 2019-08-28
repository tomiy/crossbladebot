<?php
/**
 * PHP version 7
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Core;

use CrossbladeBot\Debug\Logger;

/**
 * Registers and triggers callbacks for the defined events.
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class EventHandler
{
    /**
     * The event array.
     * 
     * [
     * 'eventName1' => ['id1' => callback1, 'id2' => callback2],
     * 'eventName2' => ['id3' => callback3, 'id4' => callback4]
     * ]
     *
     * @var array
     */
    private $_events;
    /**
     * The event ids.
     * Useful for clearing events instead of travelling the event array.
     * ['id1' => 'eventName1', 'id3' => 'eventName2']
     *
     * @var array
     */
    private $_ids;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Instantiate a new event handler.
     *
     * @param Logger $logger The logger object.
     */
    public function __construct(Logger $logger)
    {
        $this->_events = [];
        $this->_ids = [];
        $this->_logger = $logger;
    }

    /**
     * Registers an event into the pool.
     *
     * @param string   $event    The event name to register to.
     * @param callable $callback The callback to call on trigger.
     * 
     * @return string The event id.
     */
    public function register(string $event, callable $callback): string
    {
        $id = uniqid();

        if (!isset($this->_events[$event])) {
            $this->_events[$event] = [];
        }

        $this->_events[$event][$id] = $callback;
        $this->_ids[$id] = $event;

        $this->_logger->debug('Registered event ' . $id);

        return $id;
    }

    /**
     * Triggers an event and processes every attached callback.
     *
     * @param string $event   The event name to trigger.
     * @param mixed  ...$data The data to pass to the callbacks.
     * 
     * @return void
     */
    public function trigger(string $event, ...$data): void
    {
        if (!isset($this->_events[$event])) {
            return;
        }

        $this->_logger->debug('Triggered event ' . $event);

        foreach ($this->_events[$event] as $callback) {
            call_user_func($callback, ...$data);
        }
    }

    /**
     * Removes an event from the pool.
     *
     * @param string $id The event id to remove.
     * 
     * @return void
     */
    public function clear(string $id): void
    {
        if (!isset($this->_ids[$id])) {
            return;
        }
        unset($this->_events[$this->_ids[$id]][$id]);
        unset($this->_ids[$id]);

        $this->_logger->debug('Cleared event ' . $id);
    }
}
