<?php

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
