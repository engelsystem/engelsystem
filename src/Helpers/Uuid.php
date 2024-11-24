<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Stringable;

class Uuid
{
    /**
     * Generate a v4 UUID
     */
    public static function uuid(): string
    {
        return sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            mt_rand(0, 0xffffffff),
            mt_rand(0, 0xffff),
            // first bit is the uuid version, here 4
            mt_rand(0, 0x0fff) | 0x4000,
            // variant, here OSF DCE UUID
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffffffffffff)
        );
    }

    /**
     * Generate a dependent v4 UUID
     * @var string|int|float|Stringable $value any value that can be converted to string
     */
    public static function uuidBy(mixed $value, ?string $name = null): string
    {
        if (!is_null($name)) {
            if (!preg_match('/^[\da-f]+$/i', $name)) {
                throw new InvalidArgumentException('$name must be a hex string');
            }

            if (Str::length($name) > 20) {
                throw new InvalidArgumentException('$name is longer than 20 characters');
            }

            $name = Str::lower($name);
        }

        $value = $name . md5((string) $value);

        return sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            Str::substr($value, 0, 8),
            Str::substr($value, 8, 4),
            // first bit is the uuid version, here 4
            '4' . Str::substr($value, 13, 3),
            // first bit is the variant (0x8-0xb), here OSF DCE UUID
            dechex(8 + (hexdec(Str::substr($value, 16, 1)) % 4))
            . Str::substr($value, 17, 3),
            Str::substr($value, 20, 12)
        );
    }
}
