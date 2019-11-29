<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

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
    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'getUrl']),
        ];
    }

    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function getUrl(string $path, array $parameters = []): string
    {
        $path = str_replace('_', '-', $path);

        return $this->urlGenerator->to($path, $parameters);
    }
}
