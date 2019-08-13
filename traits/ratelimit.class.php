<?php

namespace CrossbladeBot\Traits;

trait RateLimit
{

    private $last;
    private $rate;
    private $span;
    private $allowance;

    public function initRate(float $rate, int $span)
    {
        $this->last = microtime(true);
        $this->setRate($rate, $span);
    }

    protected function setRate(float $rate, int $span): void
    {
        $this->rate = $rate;
        $this->allowance = $rate;
        $this->span = $span;
    }

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
