<?php

declare(strict_types=1);

namespace Engelsystem\Test\Utils;

abstract class ToStringMock
{
    public function __toString(): string
    {
        return '';
    }
}
