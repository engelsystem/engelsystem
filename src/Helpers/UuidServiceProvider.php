<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;
use Illuminate\Support\Str;

class UuidServiceProvider extends ServiceProvider
{
    /**
     * Register the UUID generator to the Str class
     */
    public function register(): void
    {
        Str::createUuidsUsing(Uuid::class . '::uuid');
    }
}
