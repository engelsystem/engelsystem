<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

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
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'getAsset']),
        ];
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAsset(string $path): string
    {
        $path = ltrim($path, '/');

        return $this->urlGenerator->to('/' . $path);
    }
}
