<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Config\Config;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Develop extends TwigExtension
{
    protected ?VarDumper $dumper = null;

    public function __construct(protected Config $config)
    {
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

    public function dump(mixed ...$vars): string
    {
        ob_start();

        foreach ($vars as $v) {
            $this->dumper ? $this->dumper->dump($v) : var_dump($v);
        }

        return ob_get_clean();
    }

    public function dd(mixed ...$vars): string
    {
        $this->flushBuffers();

        echo call_user_func_array([$this, 'dump'], $vars);

        $this->exit();

        return '';
    }

    public function setDumper(VarDumper $dumper): void
    {
        $this->dumper = $dumper;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function exit(): void
    {
        exit(1);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function flushBuffers(): void
    {
        ob_end_flush();
    }
}
