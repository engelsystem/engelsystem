<?php

namespace Engelsystem\Middleware;

use Engelsystem\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundResponse implements MiddlewareInterface
{
    /**
     * Returns a 404: Page not found response
     *
     * Should be the last middleware
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $info = _('This page could not be found or you don\'t have permission to view it. You probably have to sign in or register in order to gain access!');

        return $this->renderPage($info);
    }

    /**
     * @param string $content
     * @return Response
     * @codeCoverageIgnore
     */
    protected function renderPage($content)
    {
        global $user;
        $event_config = EventConfig();

        return response(view(__DIR__ . '/../../templates/layout.html', [
            'theme'          => isset($user) ? $user['color'] : config('theme'),
            'title'          => _('Page not found'),
            'atom_link'      => '',
            'start_page_url' => page_link_to('/'),
            'credits_url'    => page_link_to('credits'),
            'menu'           => make_menu(),
            'content'        => msg() . info($content),
            'header_toolbar' => header_toolbar(),
            'faq_url'        => config('faq_url'),
            'contact_email'  => config('contact_email'),
            'locale'         => locale(),
            'event_info'     => EventConfig_info($event_config) . ' <br />'
        ]), 404);
    }
}
