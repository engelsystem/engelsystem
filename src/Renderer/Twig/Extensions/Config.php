<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Config\Config as EngelsystemConfig;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Config extends TwigExtension
{
    public function __construct(protected EngelsystemConfig $config)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this->config, 'get']),
        ];
    }
}
