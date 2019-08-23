<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Debug\Logger;

/**
 * Registers and triggers callbacks for the defined events.
 */
class EventHandler
{
    /**
     * The event array.
     * @example['eventName1' => ['id1' => callback1, 'id2' => callback2], 'eventName2' => ['id3' => callback3, 'id4' => callback4]]
     *
     * @var array
     */
    private $events;
    /**
     * The event ids, useful for clearing events instead of travelling the multi-dimensional event array.
     * ['id1' => 'eventName1', 'id2' => 'eventName2']
     *
     * @var array
     */
    private $ids;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->events = [];
        $this->ids = [];
        $this->logger = $logger;
    }

    /**
     * Registers an event into the pool.
     *
     * @param string $event The event name to register to.
     * @param callable $callback The callback to call on trigger.
     * @return string The event id.
     */
    public function register(string $event, callable $callback): string
    {
        $id = uniqid();

        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }

        $this->events[$event][$id] = $callback;
        $this->ids[$id] = $event;

        $this->logger->info('Registered event ' . $id);

        return $id;
    }

    /**
     * Triggers an event and processes every attached callback.
     *
     * @param string $event The event name to trigger.
     * @param mixed ...$data The data to pass to the callbacks
     * @return void
     */
    public function trigger(string $event, ...$data): void
    {
        if (!isset($this->events[$event])) {
            return;
        }

        $this->logger->info('Triggered event ' . $event);

        foreach ($this->events[$event] as $callback) {
            call_user_func($callback, ...$data);
        }
    }

    /**
     * Removes an event from the pool.
     *
     * @param string $id The event id to remove.
     * @return void
     */
    public function clear(string $id): void
    {
        if (!isset($this->ids[$id])) {
            return;
        }
        unset($this->events[$this->ids[$id]][$id]);
        unset($this->ids[$id]);

        $this->logger->info('Cleared event ' . $id);
    }
}
