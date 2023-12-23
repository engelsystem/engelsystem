<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Illuminate\Support\Str as IlluminateStr;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;

class StringExtension extends TwigExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('slug', function ($title, $separator = '-', $language = 'en') {
                return IlluminateStr::slug($title, $separator, $language);
            }),
        ];
    }
}
