<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Debug\Logger;

class EventHandler
{
    private $events;
    private $ids;
    private $logger;

    public function __construct($logger)
    {
        $this->events = [];
        $this->ids = [];
        $this->logger = $logger;
    }

    public function register($event, $callback)
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

    public function trigger($event, ...$data)
    {
        if (!isset($this->events[$event])) return;

        $this->logger->info('Triggered event ' . $event);

        foreach ($this->events[$event] as $callback) {
            call_user_func($callback, ...$data);
        }
    }

    public function clear($id)
    {
        if (!isset($this->ids[$id])) return;
        unset($this->events[$this->ids[$id]][$id]);
        unset($this->ids[$id]);

        $this->logger->info('Cleared event ' . $id);
    }
}
