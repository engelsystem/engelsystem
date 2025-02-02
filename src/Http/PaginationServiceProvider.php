<?php

declare(strict_types=1);

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Illuminate\Pagination\PaginationState;

class PaginationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PaginationState::resolveUsing($this->app);
    }
}
