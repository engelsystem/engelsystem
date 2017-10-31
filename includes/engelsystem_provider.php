<?php

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Exceptions\Handler as ExceptionHandler;
use Engelsystem\Http\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * This file includes all needed functions, connects to the db etc.
 */
require_once __DIR__ . '/autoload.php';


/**
 * Initialize and bootstrap the application
 */
$app = new Application(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
$appConfig = $app->make(Config::class);
$appConfig->set(require config_path('app.php'));
$app->bootstrap($appConfig);


/**
 * Configure application
 */
date_default_timezone_set($app->get('config')->get('timezone'));

if (config('environment') == 'development') {
    $errorHandler = $app->get('error.handler');
    $errorHandler->setEnvironment(ExceptionHandler::ENV_DEVELOPMENT);
    ini_set('display_errors', true);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', false);
}


/**
 * Check for maintenance
 */
if ($app->get('config')->get('maintenance')) {
    echo file_get_contents(__DIR__ . '/../templates/maintenance.html');
    die();
}


/**
 * Initialize Request
 *
 * @var Request $request
 */
$request = Request::createFromGlobals();
$app->instance('request', $request);


/**
 * Include legacy code
 */
$includeFiles = [
    __DIR__ . '/../includes/sys_auth.php',
    __DIR__ . '/../includes/sys_form.php',
    __DIR__ . '/../includes/sys_log.php',
    __DIR__ . '/../includes/sys_menu.php',
    __DIR__ . '/../includes/sys_page.php',
    __DIR__ . '/../includes/sys_template.php',

    __DIR__ . '/../includes/model/AngelType_model.php',
    __DIR__ . '/../includes/model/EventConfig_model.php',
    __DIR__ . '/../includes/model/LogEntries_model.php',
    __DIR__ . '/../includes/model/Message_model.php',
    __DIR__ . '/../includes/model/NeededAngelTypes_model.php',
    __DIR__ . '/../includes/model/Room_model.php',
    __DIR__ . '/../includes/model/ShiftEntry_model.php',
    __DIR__ . '/../includes/model/Shifts_model.php',
    __DIR__ . '/../includes/model/ShiftsFilter.php',
    __DIR__ . '/../includes/model/ShiftSignupState.php',
    __DIR__ . '/../includes/model/ShiftTypes_model.php',
    __DIR__ . '/../includes/model/UserAngelTypes_model.php',
    __DIR__ . '/../includes/model/UserDriverLicenses_model.php',
    __DIR__ . '/../includes/model/UserGroups_model.php',
    __DIR__ . '/../includes/model/User_model.php',
    __DIR__ . '/../includes/model/ValidationResult.php',

    __DIR__ . '/../includes/view/AngelTypes_view.php',
    __DIR__ . '/../includes/view/EventConfig_view.php',
    __DIR__ . '/../includes/view/Questions_view.php',
    __DIR__ . '/../includes/view/Rooms_view.php',
    __DIR__ . '/../includes/view/ShiftCalendarLane.php',
    __DIR__ . '/../includes/view/ShiftCalendarRenderer.php',
    __DIR__ . '/../includes/view/ShiftCalendarShiftRenderer.php',
    __DIR__ . '/../includes/view/ShiftsFilterRenderer.php',
    __DIR__ . '/../includes/view/Shifts_view.php',
    __DIR__ . '/../includes/view/ShiftEntry_view.php',
    __DIR__ . '/../includes/view/ShiftTypes_view.php',
    __DIR__ . '/../includes/view/UserAngelTypes_view.php',
    __DIR__ . '/../includes/view/UserDriverLicenses_view.php',
    __DIR__ . '/../includes/view/UserHintsRenderer.php',
    __DIR__ . '/../includes/view/User_view.php',

    __DIR__ . '/../includes/controller/angeltypes_controller.php',
    __DIR__ . '/../includes/controller/event_config_controller.php',
    __DIR__ . '/../includes/controller/rooms_controller.php',
    __DIR__ . '/../includes/controller/shift_entries_controller.php',
    __DIR__ . '/../includes/controller/shifts_controller.php',
    __DIR__ . '/../includes/controller/shifttypes_controller.php',
    __DIR__ . '/../includes/controller/users_controller.php',
    __DIR__ . '/../includes/controller/user_angeltypes_controller.php',
    __DIR__ . '/../includes/controller/user_driver_licenses_controller.php',

    __DIR__ . '/../includes/helper/graph_helper.php',
    __DIR__ . '/../includes/helper/internationalization_helper.php',
    __DIR__ . '/../includes/helper/message_helper.php',
    __DIR__ . '/../includes/helper/error_helper.php',
    __DIR__ . '/../includes/helper/email_helper.php',

    __DIR__ . '/../includes/mailer/shifts_mailer.php',
    __DIR__ . '/../includes/mailer/users_mailer.php',

    __DIR__ . '/../includes/pages/admin_active.php',
    __DIR__ . '/../includes/pages/admin_arrive.php',
    __DIR__ . '/../includes/pages/admin_free.php',
    __DIR__ . '/../includes/pages/admin_groups.php',
    __DIR__ . '/../includes/pages/admin_import.php',
    __DIR__ . '/../includes/pages/admin_log.php',
    __DIR__ . '/../includes/pages/admin_questions.php',
    __DIR__ . '/../includes/pages/admin_rooms.php',
    __DIR__ . '/../includes/pages/admin_shifts.php',
    __DIR__ . '/../includes/pages/admin_user.php',
    __DIR__ . '/../includes/pages/guest_login.php',
    __DIR__ . '/../includes/pages/user_messages.php',
    __DIR__ . '/../includes/pages/user_myshifts.php',
    __DIR__ . '/../includes/pages/user_news.php',
    __DIR__ . '/../includes/pages/user_questions.php',
    __DIR__ . '/../includes/pages/user_settings.php',
    __DIR__ . '/../includes/pages/user_shifts.php',
];
foreach ($includeFiles as $file) {
    require_once realpath($file);
}


/**
 * Init application
 */
$sessionStorage = (PHP_SAPI != 'cli' ? new NativeSessionStorage(['cookie_httponly' => true]) : new MockArraySessionStorage());
$session = new Session($sessionStorage);
$app->instance('session', $session);
$session->start();
$request->setSession($session);

gettext_init();

load_auth();
