<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translation\Translator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Translation extends TwigExtension
{
    public function __construct(protected Translator $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this->translator, 'translate']),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('__', [$this->translator, 'translate']),
            new TwigFunction('_e', [$this->translator, 'translatePlural']),
        ];
    }
}
