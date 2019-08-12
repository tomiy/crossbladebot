<?php

namespace CrossbladeBot\Traits;

class RateLimit
{

    private $last;
    private $rate;
    private $span;
    private $allowance;

    public function __construct(float $rate, int $span)
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

    public function limit(int $consumed = 1): void
    {
        $current = microtime(True);
        $time_passed = $current - $this->last;
        $this->last = $current;

        $this->allowance += $time_passed * ($this->rate / $this->span);
        if ($this->allowance > $this->rate) {
            $this->allowance = $this->rate;
        }

        if ($this->allowance < $consumed) {
            $duration = ($consumed - $this->allowance) * ($this->span / $this->rate);
            $this->last += $duration;
            usleep($duration * 1000000);
            $this->allowance = 0;
        } else
            $this->allowance -= $consumed;
    }
}
