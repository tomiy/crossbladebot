<?php

namespace CrossbladeBot\Service;

use CrossbladeBot\Traits\RateLimit;

/**
 * Provides a queue system to limit and process messages in the least possible blocking way.
 * As PHP is a (mostly) synchronous stack, and Windows doesn't have process forking, a queue system is preferrable.
 */
class Queue
{
    use RateLimit;

    /**
     * The queue array that holds the data to process.
     * [microtime => 'message']
     * 
     * @var array
     */
    private $queue;

    /**
     * Pushes data into the queue.
     *
     * @param array $data the data to push.
     * @return void
     */
    protected function enqueue(array $data): void
    {
        foreach ($data as $arrayordata) {
            if (is_array($arrayordata)) {
                $this->enqueue($arrayordata);
            } else {
                $this->queue[$this->queuetime(microtime(true))] = $arrayordata;
                usleep(1);
            }
        }
    }

    /**
     * Processes the data in the queue and pushes it to a callback.
     *
     * @param array $callback a [class, function] callback array
     * @return void
     */
    protected function processqueue(array $callback): void
    {
        if(empty($this->queue)) return;
        $threshold = $this->queuetime(microtime(true) - 5);
        $this->queue = array_filter($this->queue, function ($key) use ($threshold) {
            return $key > $threshold;
        }, ARRAY_FILTER_USE_KEY);

        $data = [];
        while (sizeof($this->queue) > 0 && $this->limit()) {
            list($key) = array_keys($this->queue);
            $data[] = $this->queue[$key];
            unset($this->queue[$key]);

        }
        if(sizeof($data) > 0) {
            call_user_func($callback, $data);
        }
    }

    /**
     * Formats the time to have microseconds properly padded. (for Windows)
     *
     * @param float $time the time to process.
     * @return string the processed time string.
     */
    private function queuetime(float $time): string
    {
        return number_format($time, 6);
    }
}
