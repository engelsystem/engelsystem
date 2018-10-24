<?php

namespace Engelsystem\Middleware;

use Engelsystem\Helpers\Translator;
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
        'api',
        'atom',
        'ical',
        'login',
        'public_dashboard',
        'rooms',
        'shift_entries',
        'shifts',
        'shifts_json_export',
        'stats',
        'users',
        'user_driver_licenses',
        'user_password_recovery',
        'user_worklog'
    ];

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        global $user;
        global $privileges;
        global $page;

        /** @var Request $appRequest */
        $appRequest = $this->container->get('request');
        $page = $appRequest->query->get('p');
        if (empty($page)) {
            $page = $appRequest->path();
            $page = str_replace('-', '_', $page);
        }
        if ($page == '/') {
            $page = isset($user) ? 'news' : 'login';
        }

        $title = $content = '';
        if (
            preg_match('~^\w+$~i', $page)
            && (
                in_array($page, $this->free_pages)
                || (isset($privileges) && in_array($page, $privileges))
            )
        ) {
            list($title, $content) = $this->loadPage($page);
        }

        if (empty($title) and empty($content)) {
            /** @var Translator $translator */
            $translator = $this->container->get('translator');

            $page = 404;
            $title = $translator->translate('Page not found');
            $content = $translator->translate('This page could not be found or you don\'t have permission to view it. You probably have to sign in or register in order to gain access!');
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
        $title = ucfirst($page);
        switch ($page) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'api':
                error('Api disabled temporarily.');
                redirect(page_link_to());
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'ical':
                require_once realpath(__DIR__ . '/../../includes/pages/user_ical.php');
                user_ical();
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'atom':
                require_once realpath(__DIR__ . '/../../includes/pages/user_atom.php');
                user_atom();
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'shifts_json_export':
                require_once realpath(__DIR__ . '/../../includes/controller/shifts_controller.php');
                shifts_json_export_controller();
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'stats':
                require_once realpath(__DIR__ . '/../../includes/pages/guest_stats.php');
                guest_stats();
            case 'user_password_recovery':
                require_once realpath(__DIR__ . '/../../includes/controller/users_controller.php');
                $title = user_password_recovery_title();
                $content = user_password_recovery_controller();
                return [$title, $content];
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
            case 'news':
                $title = news_title();
                $content = user_news();
                return [$title, $content];
            case 'news_comments':
                require_once realpath(__DIR__ . '/../../includes/pages/user_news.php');
                $title = user_news_comments_title();
                $content = user_news_comments();
                return [$title, $content];
            case 'user_meetings':
                $title = meetings_title();
                $content = user_meetings();
                return [$title, $content];
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
            case 'user_messages':
                $title = messages_title();
                $content = user_messages();
                return [$title, $content];
            case 'user_questions':
                $title = questions_title();
                $content = user_questions();
                return [$title, $content];
            case 'user_settings':
                $title = settings_title();
                $content = user_settings();
                return [$title, $content];
            case 'login':
                $title = login_title();
                $content = guest_login();
                return [$title, $content];
            case 'register':
                $title = register_title();
                $content = guest_register();
                return [$title, $content];
            case 'logout':
                $title = logout_title();
                $content = guest_logout();
                return [$title, $content];
            case 'admin_questions':
                $title = admin_questions_title();
                $content = admin_questions();
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
            case 'admin_news':
                require_once realpath(__DIR__ . '/../../includes/pages/admin_news.php');
                $content = admin_news();
                return [$title, $content];
            case 'admin_rooms':
                $title = admin_rooms_title();
                $content = admin_rooms();
                return [$title, $content];
            case 'admin_groups':
                $title = admin_groups_title();
                $content = admin_groups();
                return [$title, $content];
            case 'admin_import':
                $title = admin_import_title();
                $content = admin_import();
                return [$title, $content];
            case 'admin_shifts':
                $title = admin_shifts_title();
                $content = admin_shifts();
                return [$title, $content];
            case 'admin_log':
                $title = admin_log_title();
                $content = admin_log();
                return [$title, $content];
        }

        require_once realpath(__DIR__ . '/../../includes/pages/guest_start.php');
        $content = guest_start();
        return [$title, $content];
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

        return response(view('layouts/app', [
            'title'   => $title,
            'content' => msg() . $content,
        ]), 200);
    }
}
