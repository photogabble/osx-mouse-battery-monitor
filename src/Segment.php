<?php

namespace MouseBattery;

class Segment
{
    private $start;
    private $end;
    private $items = [];
    private $min;
    private $max;

    public function __construct(int $start, int $interval = 900)
    {
        $this->start = $start;
        $this->end = $start + $interval;
    }

    public function add(int $timestamp, int $value): bool
    {
        if ($timestamp < $this->start || $timestamp > $this->end) {
            return false;
        }

        array_push($this->items, $value);

        if (is_null($this->min)) {
            $this->min = $value;
        } elseif ($value < $this->min) {
            $this->min = $value;
        }

        if (is_null($this->max)) {
            $this->max = $value;
        } elseif ($value > $this->max) {
            $this->max = $value;
        }

        return true;
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }
}
