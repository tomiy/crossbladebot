<?php

namespace CrossbladeBot\Traits;

/**
 * Provides a set of functions to limit incoming actions.
 */
trait RateLimit
{

    /**
     * The last time the rate limit was calculated.
     *
     * @var float
     */
    private $last;
    /**
     * The number of actions that can be performed during the span.
     *
     * @var float
     */
    private $rate;
    /**
     * The amount of time in seconds in which the rate is constrained.
     *
     * @var int
     */
    private $span;
    /**
     * The remaining number of actions that can be performed.
     *
     * @var float
     */
    private $allowance;

    /**
     * Set the last to now, and set the rate limit.
     *
     * @param float $rate The number of actions that can be performed during the span.
     * @param integer $span The amount of time in seconds in which the rate is constrained.
     * @return void
     */
    public function initRate(float $rate, int $span): void
    {
        $this->last = microtime(true);
        $this->setRate($rate, $span);
    }

    /**
     * Set the rate limit.
     *
     * @param float $rate The number of actions that can be performed during the span.
     * @param integer $span The amount of time in seconds in which the rate is constrained.
     * @return void
     */
    protected function setRate(float $rate, int $span): void
    {
        $this->rate = $rate;
        $this->allowance = $rate;
        $this->span = $span;
    }

    /**
     * Limit the number of actions.
     *
     * @param integer $consumed The number of actions consumed. Defaults to 1.
     * @return boolean Whether you can perform your action.
     */
    public function limit(int $consumed = 1): bool
    {
        $current = microtime(true);
        $timepassed = $current - $this->last;
        $this->last = $current;

        $this->allowance += $timepassed * ($this->rate / $this->span);
        if ($this->allowance > $this->rate) {
            $this->allowance = $this->rate;
        }

        if ($this->allowance < $consumed) {
            return false;
        }
        $this->allowance -= $consumed;
        return true;
    }
}
