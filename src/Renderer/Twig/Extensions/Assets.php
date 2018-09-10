<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

class Assets extends TwigExtension
{
    /** @var UrlGenerator */
    protected $urlGenerator;

    /**
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('asset', [$this, 'getAsset']),
        ];
    }

    /**
     * @param string $path
     * @return UrlGenerator|string
     */
    public function getAsset($path)
    {
        $path = ltrim($path, '/');

        return $this->urlGenerator->to('/' . $path);
    }
}
