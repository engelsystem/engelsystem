<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translation\Translator;
use Twig_Extension as TwigExtension;
use Twig_Filter as TwigFilter;
use Twig_Function as TwigFunction;

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
    public function getFilters()
    {
        return [
            new TwigFilter('trans', [$this->translator, 'translate']),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('__', [$this->translator, 'translate']),
            new TwigFunction('_e', [$this->translator, 'translatePlural']),
        ];
    }
}
