<?php

namespace Rpungello\Graylog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rpungello\Graylog\Graylog
 */
class Graylog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rpungello\Graylog\Graylog::class;
    }
}
