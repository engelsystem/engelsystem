<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

interface HandlerInterface
{
    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e);

    /**
     * @param Throwable $e
     * @return
     */
    public function report(Throwable $e);
}
