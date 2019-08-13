<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Debug\Logger;

class EventHandler
{
    private $events;
    private $ids;
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->events = [];
        $this->ids = [];
        $this->logger = $logger;
    }

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

    public function trigger(string $event, ...$data): array
    {
        if (!isset($this->events[$event])) return [];

        $this->logger->info('Triggered event ' . $event);

        $output = [];
        foreach ($this->events[$event] as $callback) {
            $response = call_user_func($callback, ...$data);
            if ($response) $output[] = $response;
        }

        return $output;
    }

    public function clear(string $id): void
    {
        if (!isset($this->ids[$id])) return;
        unset($this->events[$this->ids[$id]][$id]);
        unset($this->ids[$id]);

        $this->logger->info('Cleared event ' . $id);
    }
}
