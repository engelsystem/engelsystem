<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Config\Config as EngelsystemConfig;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Config extends TwigExtension
{
    /** @var EngelsystemConfig */
    protected $config;

    /**
     * @param EngelsystemConfig $config
     */
    public function __construct(EngelsystemConfig $config)
    {
        $this->config = $config;
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
