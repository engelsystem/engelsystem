<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Qr extends TwigExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('qr', [$this, 'getQr'], ['is_safe' => ['html']]),
        ];
    }

    public function getQr(string $content, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd(),
        );
        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }
}
