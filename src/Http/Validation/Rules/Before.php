<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractComparison;

class Before extends AbstractComparison
{
    use ComparesDateTime;

    protected function compare(mixed $left, mixed $right): bool
    {
        return $this->orEqual ? $left <= $right : $left < $right;
    }
}
