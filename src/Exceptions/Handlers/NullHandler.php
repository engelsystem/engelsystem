<?php

declare(strict_types=1);

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

class NullHandler extends Legacy
{
    public function render(Request $request, Throwable $e): void
    {
        return;
    }
}
