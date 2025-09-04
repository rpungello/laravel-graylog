<?php

namespace Rpungello\Graylog\Query;

use Closure;
use Stringable;

class Builder implements Stringable
{
    /**
     * @param Condition[] $conditions
     */
    public function __construct(protected array $conditions = [])
    {
    }

    public static function begin(): self
    {
        return new self();
    }

    public function and(string|Closure $field, ?string $value = null, string $operator = '='): self
    {
        if ($field instanceof Closure) {
            $child = new self();
            $field($child);
            $this->conditions[] = new Condition($child, boolean: '&&');
        } else {
            $this->conditions[] = new Condition($field, $value, $operator, '&&');
        }

        return $this;
    }

    public function or(string|Closure $field, ?string $value = null, string $operator = '='): self
    {
        if ($field instanceof Closure) {
            $child = new self();
            $field($child);
            $this->conditions[] = new Condition($child, boolean: '||');
        } else {
            $this->conditions[] = new Condition($field, $value, $operator, '||');
        }

        return $this;
    }

    public function __toString()
    {
        return substr(implode(' ', $this->conditions), 3);
    }
}
