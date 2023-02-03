<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

use Twig\Environment as Twig;
use Twig\Error\LoaderError as LoaderError;
use Twig\Error\RuntimeError as RuntimeError;
use Twig\Error\SyntaxError as SyntaxError;

class TwigEngine extends Engine
{
    public function __construct(protected Twig $twig)
    {
    }

    /**
     * Render a twig template
     *
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function get(string $path, array $data = []): string
    {
        $data = array_replace_recursive($this->sharedData, $data);

        return $this->twig->render($path, $data);
    }

    public function canRender(string $path): bool
    {
        return $this->twig->getLoader()->exists($path);
    }
}
