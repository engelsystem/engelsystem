<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Url extends TwigExtension
{
    public function __construct(protected UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'getUrl']),
        ];
    }

    public function getUrl(string $path, array $parameters = []): string
    {
        $path = str_replace('_', '-', $path);

        return $this->urlGenerator->to($path, $parameters);
    }
}
