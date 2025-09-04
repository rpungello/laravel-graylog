<?php

namespace Rpungello\Graylog\Query;

use InvalidArgumentException;
use Stringable;

class Condition implements Stringable
{
    public function __construct(public string|Builder $field, public string $value = '', public string $operator = '=', public string $boolean = '&&') {}

    public function __toString()
    {
        if ($this->field instanceof Builder) {
            return "$this->boolean ($this->field)";
        } elseif ($this->operator === 'regex') {
            return "$this->boolean $this->field:/$this->value/";
        } elseif ($this->operator === '=') {
            return "$this->boolean $this->field:\"$this->value\"";
        } elseif (in_array($this->operator, ['>', '>=', '<', '<='])) {
            return "$this->boolean $this->field:$this->operator$this->value";
        } else {
            throw new InvalidArgumentException('Unsupported condition type');
        }
    }
}
