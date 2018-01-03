<?php

use Engelsystem\Http\Request;

require_once realpath(__DIR__ . '/../includes/engelsystem.php');

$free_pages = [
    'admin_event_config',
    'angeltypes',
    'api',
    'atom',
    'credits',
    'ical',
    'login',
    'public_dashboard',
    'rooms',
    'shift_entries',
    'shifts',
    'shifts_json_export',
    'shifts_json_export_all',
    'stats',
    'users',
    'user_driver_licenses',
    'user_password_recovery',
    'user_worklog'
];

// Gewünschte Seite/Funktion
$page = '';
$title = '';
$content = '';

/** @var Request $request */
$request = $app->get('request');
$page = $request->query->get('p');
if (empty($page)) {
    $page = $request->path();
    $page = str_replace('-', '_', $page);
}
if ($page == '/') {
    $page = isset($user) ? 'news' : 'login';
}

if (
    preg_match('/^\w*$/i', $page)
    && (
        in_array($page, $free_pages)
        || (isset($privileges) && in_array($page, $privileges))
    )
) {
    $title = $page;

    switch ($page) {
        case 'api':
            error('Api disabled temporarily.');
            redirect(page_link_to());
            break;
        case 'ical':
            require_once realpath(__DIR__ . '/../includes/pages/user_ical.php');
            user_ical();
            break;
        case 'atom':
            require_once realpath(__DIR__ . '/../includes/pages/user_atom.php');
            user_atom();
            break;
        case 'shifts_json_export':
            require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
            shifts_json_export_controller();
            break;
        case 'shifts_json_export_all':
            require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
            shifts_json_export_all_controller();
            break;
        case 'stats':
            require_once realpath(__DIR__ . '/../includes/pages/guest_stats.php');
            guest_stats();
            break;
        case 'user_password_recovery':
            require_once realpath(__DIR__ . '/../includes/controller/users_controller.php');
            $title = user_password_recovery_title();
            $content = user_password_recovery_controller();
            break;
        case 'public_dashboard':
            list($title, $content) = public_dashboard_controller();
            break;
        case 'angeltypes':
            list($title, $content) = angeltypes_controller();
            break;
        case 'shift_entries':
            list($title, $content) = shift_entries_controller();
            break;
        case 'shifts':
            list($title, $content) = shifts_controller();
            break;
        case 'users':
            list($title, $content) = users_controller();
            break;
        case 'user_angeltypes':
            list($title, $content) = user_angeltypes_controller();
            break;
        case 'user_driver_licenses':
            list($title, $content) = user_driver_licenses_controller();
            break;
        case 'shifttypes':
            list($title, $content) = shifttypes_controller();
            break;
        case 'admin_event_config':
            list($title, $content) = event_config_edit_controller();
            break;
        case 'rooms':
            list($title, $content) = rooms_controller();
            break;
        case 'news':
            $title = news_title();
            $content = user_news();
            break;
        case 'news_comments':
            require_once realpath(__DIR__ . '/../includes/pages/user_news.php');
            $title = user_news_comments_title();
            $content = user_news_comments();
            break;
        case 'user_meetings':
            $title = meetings_title();
            $content = user_meetings();
            break;
        case 'user_myshifts':
            $title = myshifts_title();
            $content = user_myshifts();
            break;
        case 'user_shifts':
            $title = shifts_title();
            $content = user_shifts();
            break;
        case 'user_worklog':
            list($title, $content) = user_worklogs_controller();
            break;
        case 'user_messages':
            $title = messages_title();
            $content = user_messages();
            break;
        case 'user_questions':
            $title = questions_title();
            $content = user_questions();
            break;
        case 'user_settings':
            $title = settings_title();
            $content = user_settings();
            break;
        case 'login':
            $title = login_title();
            $content = guest_login();
            break;
        case 'register':
            $title = register_title();
            $content = guest_register();
            break;
        case 'logout':
            $title = logout_title();
            $content = guest_logout();
            break;
        case 'admin_questions':
            $title = admin_questions_title();
            $content = admin_questions();
            break;
        case 'admin_user':
            $title = admin_user_title();
            $content = admin_user();
            break;
        case 'admin_arrive':
            $title = admin_arrive_title();
            $content = admin_arrive();
            break;
        case 'admin_active':
            $title = admin_active_title();
            $content = admin_active();
            break;
        case 'admin_free':
            $title = admin_free_title();
            $content = admin_free();
            break;
        case 'admin_news':
            require_once realpath(__DIR__ . '/../includes/pages/admin_news.php');
            $content = admin_news();
            break;
        case 'admin_rooms':
            $title = admin_rooms_title();
            $content = admin_rooms();
            break;
        case 'admin_groups':
            $title = admin_groups_title();
            $content = admin_groups();
            break;
        case 'admin_import':
            $title = admin_import_title();
            $content = admin_import();
            break;
        case 'admin_shifts':
            $title = admin_shifts_title();
            $content = admin_shifts();
            break;
        case 'admin_log':
            $title = admin_log_title();
            $content = admin_log();
            break;
        case 'credits':
            require_once realpath(__DIR__ . '/../includes/pages/guest_credits.php');
            $title = credits_title();
            $content = guest_credits();
            break;
        default:
            require_once realpath(__DIR__ . '/../includes/pages/guest_start.php');
            $content = guest_start();
            break;
    }
} else {
    // Wenn schon eingeloggt, keine-Berechtigung-Seite anzeigen
    if (isset($user)) {
        $title = _('No Access');
        $content = _('You don\'t have permission to view this page . You probably have to sign in or register in order to gain access!');
    } else {
        // Sonst zur Loginseite leiten
        redirect(page_link_to('login'));
    }
}

$event_config = EventConfig();

$parameters = [
    'key' => (isset($user) ? $user['api_key'] : ''),
];
if ($page == 'user_meetings') {
    $parameters['meetings'] = 1;
}

echo view(__DIR__ . '/../templates/layout.html', [
    'theme'          => isset($user) ? $user['color'] : config('theme'),
    'title'          => $title,
    'atom_link'      => ($page == 'news' || $page == 'user_meetings')
        ? ' <link href="'
        . page_link_to('atom', $parameters)
        . '" type = "application/atom+xml" rel = "alternate" title = "Atom Feed">'
        : '',
    'start_page_url' => page_link_to('/'),
    'credits_url'    => page_link_to('credits'),
    'menu'           => make_menu(),
    'content'        => msg() . $content,
    'header_toolbar' => header_toolbar(),
    'faq_url'        => config('faq_url'),
    'contact_email'  => config('contact_email'),
    'locale'         => locale(),
    'event_info'     => EventConfig_info($event_config) . ' <br />'
]);
