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

namespace crossbladebot\basic;

/**
 * Provides a set of functions to limit incoming actions.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
trait RateLimit
{

    /**
     * The last time the rate limit was calculated.
     *
     * @var float
     */
    private float $_last;
    /**
     * The number of actions that can be performed during the span.
     *
     * @var float
     */
    private float $_rate;
    /**
     * The amount of time in seconds in which the rate is constrained.
     *
     * @var int
     */
    private int $_span;
    /**
     * The remaining number of actions that can be performed.
     *
     * @var float
     */
    private float $_allowance;

    /**
     * Set the last to now, and set the rate limit.
     *
     * @param float $rate # of actions that can be performed during the span.
     * @param integer $span Time in seconds in which the rate is constrained.
     *
     * @return void
     */
    public function initRate(float $rate, int $span): void
    {
        $this->_last = microtime(true);
        $this->setRate($rate, $span);
    }

    /**
     * Set the rate limit.
     *
     * @param float $rate # of actions that can be performed during the span.
     * @param integer $span Time in seconds in which the rate is constrained.
     *
     * @return void
     */
    protected function setRate(float $rate, int $span): void
    {
        $this->_rate = $rate;
        $this->_allowance = $rate;
        $this->_span = $span;
    }

    /**
     * Limit the number of actions.
     *
     * @param integer $consumed The number of actions consumed. Defaults to 1.
     *
     * @return bool Whether you can perform your action.
     */
    public function limit(int $consumed = 1): bool
    {
        $current = microtime(true);
        $timePassed = $current - $this->_last;
        $this->_last = $current;

        $this->_allowance += $timePassed * ($this->_rate / $this->_span);
        if ($this->_allowance > $this->_rate) {
            $this->_allowance = $this->_rate;
        }

        if ($this->_allowance < $consumed) {
            return false;
        }
        $this->_allowance -= $consumed;
        return true;
    }
}
