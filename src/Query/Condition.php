<?php

namespace Rpungello\Graylog\Query;

use Stringable;

class Condition implements Stringable
{
    public function __construct(public string|Builder $field, public string $value = '', public string $operator = '=', public string $boolean = '&&')
    {
    }

    public function __toString()
    {
        if ($this->field instanceof Builder) {
            return "$this->boolean ($this->field)";
        } else {
            return "$this->boolean $this->field:\"$this->value\"";
        }
    }
}
