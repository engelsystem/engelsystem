<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to support the old routing / pages from includes
 */
class LegacyMiddleware implements MiddlewareInterface
{
    /** @var array<string> */
    protected array $free_pages = [
        'angeltypes',
        'public_dashboard',
        'locations',
        'shift_entries',
        'shifts',
        'users',
    ];

    public function __construct(protected ContainerInterface $container, protected Authenticator $auth)
    {
    }

    /**
     * Handle the request the old way
     *
     * Should be used before a 404 is sent
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var Request $appRequest */
        $appRequest = $this->container->get('request');
        $page = $appRequest->query->get('p');
        // Support old URL/permission scheme
        if (empty($page)) {
            $page = $appRequest->path();
            $page = str_replace('-', '_', $page);
        }

        $allowPage = false;
        if ($page === 'admin_arrive') {
            $allowPage = $this->auth->can('users.arrive.list');
        }

        $title = $content = '';
        if (
            preg_match('~^\w+$~i', $page)
            && (in_array($page, $this->free_pages) || $this->auth->can($page) || $allowPage)
        ) {
            list($title, $content) = $this->loadPage($page);
        }

        if ($content instanceof ResponseInterface) {
            return $content;
        }

        if (empty($title) and empty($content)) {
            /** @var Translator $translator */
            $translator = $this->container->get('translator');

            $page = 404;
            $title = $translator->translate('page.404.title');
            $content = $translator->translate('page.404.text');
        }

        return $this->renderPage($page, $title, $content);
    }

    /**
     * Get the legacy page content and title
     *
     * @return array ['title', 'content']
     * @codeCoverageIgnore
     */
    protected function loadPage(string $page): array
    {
        switch ($page) {
            case 'public_dashboard':
                return public_dashboard_controller();
            case 'angeltypes':
                return angeltypes_controller();
            case 'shift_entries':
                return shift_entries_controller();
            case 'shifts':
                return shifts_controller();
            case 'users':
                return users_controller();
            case 'user_angeltypes':
                return user_angeltypes_controller();
            case 'locations':
                return locations_controller();
            case 'user_myshifts':
                $title = myshifts_title();
                $content = user_myshifts();
                return [$title, $content];
            case 'user_shifts':
                $title = shifts_title();
                $content = user_shifts();
                return [$title, $content];
            case 'admin_user':
                $title = admin_user_title();
                $content = admin_user();
                return [$title, $content];
            case 'admin_arrive':
                $title = admin_arrive_title();
                $content = admin_arrive();
                return [$title, $content];
            case 'admin_active':
                $title = admin_active_title();
                $content = admin_active();
                return [$title, $content];
            case 'admin_free':
                $title = admin_free_title();
                $content = admin_free();
                return [$title, $content];
            case 'admin_groups':
                $title = admin_groups_title();
                $content = admin_groups();
                return [$title, $content];
            case 'admin_shifts':
                $title = admin_shifts_title();
                $content = admin_shifts();
                return [$title, $content];
        }

        throw_redirect(url('/login'));

        return [];
    }

    /**
     * Render the template
     *
     * @codeCoverageIgnore
     */
    protected function renderPage(string | int $page, string $title, string $content): ResponseInterface
    {
        if (!empty($page) && is_int($page)) {
            return response($content, $page);
        }

        if (str_contains($content, '<html')) {
            return response($content);
        }

        return response(
            view(
                'layouts/app',
                [
                    'title'   => $title,
                    'content' => msg() . $content,
                ]
            )
        );
    }
}
