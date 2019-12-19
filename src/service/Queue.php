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

namespace crossbladebot\service;

use crossbladebot\basic\RateLimit;

/**
 * Provides a queue system to process messages in the least possible blocking way.
 * As PHP is a (mostly) synchronous stack,
 * and Windows doesn't have process forking,
 * a queue system is preferrable.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
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
    private array $_queue;

    /**
     * Pushes data into the queue.
     *
     * @param array $data the data to push.
     *
     * @return void
     */
    protected function enqueue(array $data): void
    {
        foreach ($data as $arrayOrData) {
            if (is_array($arrayOrData)) {
                $this->enqueue($arrayOrData);
                continue;
            }
            $this->_queue[$this->_queueTime(microtime(true))] = $arrayOrData;
            usleep(1);
        }
    }

    /**
     * Formats the time to have microseconds properly padded. (for Windows)
     *
     * @param float $time the time to process.
     *
     * @return string the processed time string.
     */
    private function _queueTime(float $time): string
    {
        return number_format($time, 6);
    }

    /**
     * Processes the data in the queue and pushes it to a callback.
     *
     * @param array $callback a [class, function] callback array.
     *
     * @return int the number of units of data processed
     */
    protected function processQueue(array $callback): int
    {
        if (empty($this->_queue)) {
            return 0;
        }
        $threshold = $this->_queueTime(microtime(true) - 5);
        $this->_queue = array_filter(
            $this->_queue,
            function ($key) use ($threshold) {
                return $key > $threshold;
            },
            ARRAY_FILTER_USE_KEY
        );

        $data = [];
        while (count($this->_queue) > 0 && $this->limit()) {
            list($key) = array_keys($this->_queue);
            $data[] = $this->_queue[$key];
            unset($this->_queue[$key]);
        }
        $dataSize = count($data);
        if ($dataSize > 0) {
            call_user_func($callback, $data);
        }

        return $dataSize;
    }
}
