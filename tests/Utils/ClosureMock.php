<?php

declare(strict_types=1);

namespace Engelsystem\Test\Utils;

abstract class ClosureMock
{
    public function __invoke(): mixed
    {
        return null;
    }
}
