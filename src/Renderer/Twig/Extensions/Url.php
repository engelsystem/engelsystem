<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

class Url extends TwigExtension
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
            new TwigFunction('url', [$this, 'getUrl']),
        ];
    }

    /**
     * @param string $path
     * @param array  $parameters
     * @return UrlGenerator|string
     */
    public function getUrl($path, $parameters = [])
    {
        $path = str_replace('_', '-', $path);

        return $this->urlGenerator->to($path, $parameters);
    }
}
