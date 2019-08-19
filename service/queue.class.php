<?php

namespace CrossbladeBot\Service;

use CrossbladeBot\Traits\RateLimit;

class Queue
{
    use RateLimit;

    private $queue;

    protected function enqueue(array $data): void
    {
        foreach ($data as $arrayormessage) {
            if (is_array($arrayormessage)) {
                $this->enqueue($arrayormessage);
            } else {
                $this->queue[$this->queuetime(microtime(true))] = $arrayormessage;
                usleep(1);
            }
        }
    }

    protected function processqueue(array $callback): void
    {
        if(empty($this->queue)) return;
        $threshold = $this->queuetime(microtime(true) - 5);
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

    private function queuetime(float $time): string
    {
        return number_format($time, 6);
    }
}
