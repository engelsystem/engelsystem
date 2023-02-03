<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Csrf extends TwigExtension
{
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf', [$this, 'getCsrfField'], ['is_safe' => ['html']]),
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];
    }

    public function getCsrfField(): string
    {
        return sprintf('<input type="hidden" name="_token" value="%s">', $this->getCsrfToken());
    }

    public function getCsrfToken(): string
    {
        return $this->session->get('_token');
    }
}
