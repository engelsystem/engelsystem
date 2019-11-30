<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Csrf extends TwigExtension
{
    /** @var SessionInterface */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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

    /**
     * @return string
     */
    public function getCsrfField(): string
    {
        return sprintf('<input type="hidden" name="_token" value="%s">', $this->getCsrfToken());
    }

    /**
     * @return string
     */
    public function getCsrfToken(): string
    {
        return $this->session->get('_token');
    }
}
