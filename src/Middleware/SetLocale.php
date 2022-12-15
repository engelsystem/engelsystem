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
    public function __construct(
        protected Translator $translator,
        protected Session $session,
        protected Authenticator $auth
    ) {
    }

    /**
     * Process an incoming server request and setting the locale if required
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
