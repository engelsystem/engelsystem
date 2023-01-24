<?php

declare(strict_types=1);

namespace Engelsystem\Database\Migration;

enum Direction: string
{
    case UP = 'up';
    case DOWN = 'down';
}
