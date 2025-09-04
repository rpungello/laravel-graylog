<?php

namespace Rpungello\Graylog\TimeRange;

class Relative extends TimeRange
{
    public function __construct(protected int $numberOfSeconds)
    {
    }

    public function toArray(): array
    {
        return [
            'type' => 'relative',
            'range' => $this->numberOfSeconds,
        ];
    }
}
