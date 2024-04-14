<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

use Illuminate\Support\Str;
use Twig\Error\LoaderError as ErrorLoader;

class TwigTextLoader extends TwigLoader
{
    /**
     * @throws ErrorLoader
     */
    public function findTemplate(string $name, bool $throw = true): ?string
    {
        if (!Str::endsWith($name, '.text.twig') && !Str::endsWith($name, '.text')) {
            $name .= '.text';
        }

        return parent::findTemplate($name, $throw);
    }
}
