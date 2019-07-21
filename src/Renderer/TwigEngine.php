<?php

namespace Engelsystem\Renderer;

use Twig_Environment as Twig;
use Twig_Error_Loader as LoaderError;
use Twig_Error_Runtime as RuntimeError;
use Twig_Error_Syntax as SyntaxError;

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
