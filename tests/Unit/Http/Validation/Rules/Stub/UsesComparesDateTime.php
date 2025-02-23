<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules\Stub;

use Engelsystem\Http\Validation\Rules\ComparesDateTime;

class UsesComparesDateTime
{
    use ComparesDateTime;

    protected mixed $callback = null;

    public function compare(mixed $left, mixed $right): bool
    {
        /** @var callable $callback */
        $callback = $this->callback;
        return $callback($left, $right);
    }

    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function getCompareTo(): mixed
    {
        return $this->compareTo;
    }

    public function getOrEqual(): bool
    {
        return $this->orEqual;
    }
}
