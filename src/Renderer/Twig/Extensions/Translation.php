<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translation\Translator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Translation extends TwigExtension
{
    /** @var Translator */
    protected $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
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
