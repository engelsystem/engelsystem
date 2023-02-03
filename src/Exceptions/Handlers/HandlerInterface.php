<?php

declare(strict_types=1);

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

interface HandlerInterface
{
    public function render(Request $request, Throwable $e): void;

    public function report(Throwable $e): void;
}
