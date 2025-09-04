<?php

namespace Rpungello\Graylog\TimeRange;

use DateTimeInterface;

class Absolute extends TimeRange
{
    public function __construct(protected DateTimeInterface $start, protected DateTimeInterface $end) {}

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'type' => 'absolute',
            'from' => $this->start->format('c'),
            'to' => $this->end->format('c'),
        ];
    }
}
