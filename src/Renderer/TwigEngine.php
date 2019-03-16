<?php

namespace Engelsystem\Renderer;

use Twig\Environment as Twig;
use Twig\Error\LoaderError as LoaderError;
use Twig\Error\RuntimeError as RuntimeError;
use Twig\Error\SyntaxError as SyntaxError;

class TwigEngine extends Engine
{
    /** @var Twig */
    protected $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Render a twig template
     *
     * @param string $path
     * @param array  $data
     * @return string
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function get(string $path, array $data = []): string
    {
        $data = array_replace_recursive($this->sharedData, $data);

        return $this->twig->render($path, $data);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function canRender(string $path): bool
    {
        return $this->twig->getLoader()->exists($path);
    }
}
