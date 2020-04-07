<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Assets extends TwigExtension
{
    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
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
