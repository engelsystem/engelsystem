<?php

namespace Engelsystem\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LegacyMiddleware implements MiddlewareInterface
{
    protected $free_pages = [
        'admin_event_config',
        'angeltypes',
        'atom',
        'ical',
        'public_dashboard',
        'rooms',
        'shift_entries',
        'shifts',
        'shifts_json_export',
        'users',
        'user_driver_licenses',
        'user_worklog',
        'admin_shifts_history',
    ];

    /** @var ContainerInterface */
    protected $container;

    /** @var Authenticator */
    protected $auth;

    /**
     * @param ContainerInterface $container
     * @param Authenticator      $auth
     */
    public function __construct(ContainerInterface $container, Authenticator $auth)
    {
        $this->container = $container;
        $this->auth = $auth;
    }

    /**
     * Handle the request the old way
     *
     * Should be used before a 404 is send
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var Request $appRequest */
        $appRequest = $this->container->get('request');
        $page = $appRequest->query->get('p');
        if (empty($page)) {
            $page = $appRequest->path();
            $page = str_replace('-', '_', $page);
        }

        $title = $content = '';
        if (
            preg_match('~^\w+$~i', $page)
            && (in_array($page, $this->free_pages) || $this->auth->can($page))
        ) {
            list($title, $content) = $this->loadPage($page);
        }

        if (empty($title) and empty($content)) {
            /** @var Translator $translator */
            $translator = $this->container->get('translator');

            $page = 404;
            $title = $translator->translate('Page not found');
            $content = $translator->translate('page.404.text');
        }

        return $this->renderPage($page, $title, $content);
    }

    /**
     * Get the legacy page content and title
     *
     * @param string $page
     * @return array ['title', 'content']
     * @codeCoverageIgnore
     */
    protected function loadPage($page)
    {
        switch ($page) {
            case 'ical':
                require_once realpath(__DIR__ . '/../../includes/pages/user_ical.php');
                user_ical();
                break;
            case 'atom':
                require_once realpath(__DIR__ . '/../../includes/pages/user_atom.php');
                user_atom();
                break;
            case 'shifts_json_export':
                require_once realpath(__DIR__ . '/../../includes/controller/shifts_controller.php');
                shifts_json_export_controller();
                break;
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
            case 'user_driver_licenses':
                return user_driver_licenses_controller();
            case 'shifttypes':
                list($title, $content) = shifttypes_controller();
                return [$title, $content];
            case 'admin_event_config':
                list($title, $content) = event_config_edit_controller();
                return [$title, $content];
            case 'rooms':
                return rooms_controller();
            case 'user_myshifts':
                $title = myshifts_title();
                $content = user_myshifts();
                return [$title, $content];
            case 'user_shifts':
                $title = shifts_title();
                $content = user_shifts();
                return [$title, $content];
            case 'user_worklog':
                return user_worklog_controller();
            case 'user_settings':
                $title = settings_title();
                $content = user_settings();
                return [$title, $content];
            case 'register':
                $title = register_title();
                $content = guest_register();
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
            case 'admin_rooms':
                $title = admin_rooms_title();
                $content = admin_rooms();
                return [$title, $content];
            case 'admin_groups':
                $title = admin_groups_title();
                $content = admin_groups();
                return [$title, $content];
            case 'admin_shifts':
                $title = admin_shifts_title();
                $content = admin_shifts();
                return [$title, $content];
            case 'admin_shifts_history':
                return [admin_shifts_history_title(), admin_shifts_history()];
        }

        throw_redirect(page_link_to('login'));

        return [];
    }

    /**
     * Render the template
     *
     * @param string $page
     * @param string $title
     * @param string $content
     * @return Response
     * @codeCoverageIgnore
     */
    protected function renderPage($page, $title, $content)
    {
        if (!empty($page) && is_int($page)) {
            return response($content, (int)$page);
        }

        if (strpos((string)$content, '<html') !== false) {
            return response($content);
        }

        return response(
            view(
                'layouts/app',
                [
                    'title'   => $title,
                    'content' => msg() . $content,
                ]
            ),
            200
        );
    }
}
