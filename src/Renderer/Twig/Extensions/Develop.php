<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Config\Config;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Develop extends TwigExtension
{
    /** @var Config */
    protected $config;

    /** @var VarDumper|null */
    protected $dumper;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        if ($this->config->get('environment') != 'development') {
            return [];
        }

        return [
            new TwigFunction('dump', [$this, 'dump'], ['is_safe' => ['html']]),
            new TwigFunction('dd', [$this, 'dd']),
        ];
    }

    /**
     * @param mixed $vars
     * @return string
     */
    public function dump(...$vars): string
    {
        ob_start();

        foreach ($vars as $v) {
            $this->dumper ? $this->dumper->dump($v) : var_dump($v);
        }

        return ob_get_clean();
    }

    /**
     * @param mixed $vars
     * @return string
     */
    public function dd(...$vars): string
    {
        $this->flushBuffers();

        echo call_user_func_array([$this, 'dump'], $vars);

        $this->exit();

        return '';
    }

    /**
     * @param VarDumper $dumper
     */
    public function setDumper($dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function exit()
    {
        exit(1);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function flushBuffers()
    {
        ob_end_flush();
    }
}
