<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Illuminate\Support\Str;

class Color
{
    protected int $r;
    protected int $g;
    protected int $b;

    public function __construct(string $color)
    {
        $color = ltrim($color, '#');
        if (Str::length($color) < 3) {
            $color = '000';
        }
        if (Str::length($color) < 6) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        $this->r = hexdec(substr($color, 0, 2));
        $this->g = hexdec(substr($color, 2, 2));
        $this->b = hexdec(substr($color, 4, 2));
    }

    public function __toString(): string
    {
        return '#'
            . str_pad(dechex($this->r), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($this->g), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($this->b), 2, '0', STR_PAD_LEFT);
    }

    public function isLight(): bool
    {
        return $this->brightness() > .5;
    }

    public function brightness(): float
    {
        return sqrt(
            0.299 * $this->r ** 2
                + 0.587 * $this->g ** 2
                + 0.114 * $this->b ** 2
        ) / 255;
    }

    public static function random(): self
    {
        return new self(
            str_pad(
                dechex(rand(0, 0xffffff)),
                6,
                '0',
                STR_PAD_LEFT
            )
        );
    }
}
