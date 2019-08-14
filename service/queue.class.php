<?php

namespace CrossbladeBot\Service;

use CrossbladeBot\Traits\RateLimit;

class Queue
{
    use RateLimit;

    protected static $queuetimeout = 5;
    private $queue;

    public function enqueue(array $data): void
    {
        foreach ($data as $arrayormessage) {
            if (is_array($arrayormessage)) {
                $this->enqueue($arrayormessage);
            } else {
                $this->queue[microtime(true) * 1E4] = $arrayormessage;
                usleep(100);
            }
        }
    }

    protected function processqueue(array $callback): void
    {
        $threshold = (microtime(true) - static::$queuetimeout) * 1E4;
        $this->queue = array_filter($this->queue, function ($key) use ($threshold) {
            return $key > $threshold;
        }, ARRAY_FILTER_USE_KEY);

        while (sizeof($this->queue) > 0 && $this->limit()) {
            list($key) = array_keys($this->queue);
            $message = [$this->queue[$key]];
            unset($this->queue[$key]);

            call_user_func($callback, $message);
        }
    }
}
