<?php

namespace Rpungello\Graylog\TimeRange;

use Carbon\CarbonInterval;

class Relative extends TimeRange
{
    public function __construct(protected int|CarbonInterval $interval) {}

    public function toArray(): array
    {
        return [
            'type' => 'relative',
            'range' => $this->getIntervalAsNumberOfSeconds(),
        ];
    }

    private function getIntervalAsNumberOfSeconds(): int
    {
        if (is_int($this->interval)) {
            return $this->interval;
        } else {
            return $this->interval->totalSeconds;
        }
    }
}
