<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Color as ColorHelper;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Color extends TwigExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('color', [$this, 'getColor'], ['is_safe' => ['html']]),
        ];
    }

    public function getColor(mixed $color): ColorHelper
    {
        return new ColorHelper((string) $color);
    }
}
