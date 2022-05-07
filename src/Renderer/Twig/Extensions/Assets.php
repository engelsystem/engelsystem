<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Assets as AssetsProvider;
use Engelsystem\Http\UrlGeneratorInterface;
use Illuminate\Support\Str;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Assets extends TwigExtension
{
    /** @var AssetsProvider */
    protected $assets;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param AssetsProvider $assets
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(AssetsProvider $assets, UrlGeneratorInterface $urlGenerator)
    {
        $this->assets = $assets;
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
        if (Str::startsWith($path, 'assets/')) {
            $asset = Str::replaceFirst('assets/', '', $path);
            $path = 'assets/' . $this->assets->getAssetPath($asset);
        }

        return $this->urlGenerator->to('/' . $path);
    }
}
