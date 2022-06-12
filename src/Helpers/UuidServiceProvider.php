<?php

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;
use Illuminate\Support\Str;

class UuidServiceProvider extends ServiceProvider
{
    /**
     * Register the UUID generator to the Str class
     */
    public function register()
    {
        Str::createUuidsUsing([$this, 'uuid']);
    }

    /**
     * Generate a v4 UUID
     */
    public function uuid(): string
    {
        return sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            mt_rand(0, 0xffffffff),
            mt_rand(0, 0xffff),
            // first bit is the uuid version, here 4
            mt_rand(0, 0x0fff) | 0x4000,
            // variant
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffffffffffff)
        );
    }
}
