<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translator;
use Twig_Extension as TwigExtension;
use Twig_Extensions_TokenParser_Trans as TranslationTokenParser;
use Twig_Filter as TwigFilter;
use Twig_Function as TwigFunction;
use Twig_TokenParserInterface as TwigTokenParser;

class Translation extends TwigExtension
{
    /** @var Translator */
    protected $translator;

    /** @var TranslationTokenParser */
    protected $tokenParser;

    /**
     * @param Translator             $translator
     * @param TranslationTokenParser $tokenParser
     */
    public function __construct(Translator $translator, TranslationTokenParser $tokenParser)
    {
        $this->translator = $translator;
        $this->tokenParser = $tokenParser;
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

    /**
     * @return TwigTokenParser[]
     */
    public function getTokenParsers()
    {
        return [$this->tokenParser];
    }
}
