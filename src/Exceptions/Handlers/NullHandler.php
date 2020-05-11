<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

class NullHandler extends Legacy
{
    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e)
    {
        return;
    }
}
