<?php

namespace Engelsystem\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SetLocale implements MiddlewareInterface
{
    /** @var Authenticator */
    protected $auth;

    /** @var Translator */
    protected $translator;

    /** @var Session */
    protected $session;

    /**
     * @param Translator    $translator
     * @param Session       $session
     * @param Authenticator $auth
     */
    public function __construct(Translator $translator, Session $session, Authenticator $auth)
    {
        $this->auth = $auth;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * Process an incoming server request and setting the locale if required
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $query = $request->getQueryParams();
        if (isset($query['set-locale']) && $this->translator->hasLocale($query['set-locale'])) {
            $locale = $query['set-locale'];

            $this->translator->setLocale($locale);
            $this->session->set('locale', $locale);

            $user = $this->auth->user();
            if ($user) {
                $user->settings->language = $locale;
                $user->settings->save();
            }
        }

        return $handler->handle($request);
    }
}
