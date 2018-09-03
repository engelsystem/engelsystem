<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

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
    public function getFunctions()
    {
        return [
            new TwigFunction('csrf', [$this, 'getCsrfField'], ['is_safe' => ['html']]),
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];
    }

    /**
     * @return string
     */
    public function getCsrfField()
    {
        return sprintf('<input type="hidden" name="_token" value="%s">', $this->getCsrfToken());
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->session->get('_token');
    }
}
