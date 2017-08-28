<?php

use Engelsystem\Config\Config;
use Engelsystem\Database\Db;
use Engelsystem\Exceptions\Handler as ExceptionHandler;
use Engelsystem\Http\Request;
use Engelsystem\Renderer\HtmlEngine;
use Engelsystem\Renderer\Renderer;

/**
 * This file includes all needed functions, connects to the db etc.
 */

require_once __DIR__ . '/autoload.php';

/**
 * Load configuration
 */
$config = new Config();
Config::setInstance($config);
$config->set(require __DIR__ . '/../config/config.default.php');

if (file_exists(__DIR__ . '/../config/config.php')) {
    $config->set(array_replace_recursive(
        $config->get(null),
        require __DIR__ . '/../config/config.php'
    ));
}

date_default_timezone_set($config->get('timezone'));


/**
 * Initialize Request
 */
$request = new Request();
$request->create($_GET, $_POST, $_SERVER, config('url'));
$request::setInstance($request);

/**
 * Check for maintenance
 */
if ($config->get('maintenance')) {
    echo file_get_contents(__DIR__ . '/../templates/maintenance.html');
    die();
}


/**
 * Initialize renderer
 */
$renderer = new Renderer();
$renderer->addRenderer(new HtmlEngine());
Renderer::setInstance($renderer);


/**
 * Register error handler
 */
$errorHandler = new ExceptionHandler();
if (config('environment') == 'development') {
    $errorHandler->setEnvironment(ExceptionHandler::ENV_DEVELOPMENT);
    ini_set('display_errors', true);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', false);
}


/**
 * Connect to database
 */
Db::connect(
    'mysql:host=' . config('database')['host'] . ';dbname=' . config('database')['db'] . ';charset=utf8',
    config('database')['user'],
    config('database')['pw']
) || die('Error: Unable to connect to database');
Db::getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
Db::getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


/**
 * Include legacy code
 */
require_once realpath(__DIR__ . '/../includes/sys_auth.php');
require_once realpath(__DIR__ . '/../includes/sys_form.php');
require_once realpath(__DIR__ . '/../includes/sys_log.php');
require_once realpath(__DIR__ . '/../includes/sys_menu.php');
require_once realpath(__DIR__ . '/../includes/sys_page.php');
require_once realpath(__DIR__ . '/../includes/sys_template.php');

require_once realpath(__DIR__ . '/../includes/model/AngelType_model.php');
require_once realpath(__DIR__ . '/../includes/model/EventConfig_model.php');
require_once realpath(__DIR__ . '/../includes/model/LogEntries_model.php');
require_once realpath(__DIR__ . '/../includes/model/Message_model.php');
require_once realpath(__DIR__ . '/../includes/model/NeededAngelTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/Room_model.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftEntry_model.php');
require_once realpath(__DIR__ . '/../includes/model/Shifts_model.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftsFilter.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftSignupState.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/UserAngelTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/UserDriverLicenses_model.php');
require_once realpath(__DIR__ . '/../includes/model/UserGroups_model.php');
require_once realpath(__DIR__ . '/../includes/model/User_model.php');
require_once realpath(__DIR__ . '/../includes/model/ValidationResult.php');

require_once realpath(__DIR__ . '/../includes/view/AngelTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/EventConfig_view.php');
require_once realpath(__DIR__ . '/../includes/view/Questions_view.php');
require_once realpath(__DIR__ . '/../includes/view/Rooms_view.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftCalendarLane.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftCalendarRenderer.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftCalendarShiftRenderer.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftsFilterRenderer.php');
require_once realpath(__DIR__ . '/../includes/view/Shifts_view.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftEntry_view.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/UserAngelTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/UserDriverLicenses_view.php');
require_once realpath(__DIR__ . '/../includes/view/UserHintsRenderer.php');
require_once realpath(__DIR__ . '/../includes/view/User_view.php');

require_once realpath(__DIR__ . '/../includes/controller/angeltypes_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/event_config_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/rooms_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/shift_entries_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/shifttypes_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/users_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/user_angeltypes_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/user_driver_licenses_controller.php');

require_once realpath(__DIR__ . '/../includes/helper/graph_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/internationalization_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/message_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/error_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/email_helper.php');

require_once realpath(__DIR__ . '/../includes/mailer/shifts_mailer.php');
require_once realpath(__DIR__ . '/../includes/mailer/users_mailer.php');

require_once realpath(__DIR__ . '/../includes/pages/admin_active.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_arrive.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_free.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_groups.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_import.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_log.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_questions.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_rooms.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_shifts.php');
require_once realpath(__DIR__ . '/../includes/pages/admin_user.php');
require_once realpath(__DIR__ . '/../includes/pages/guest_login.php');
require_once realpath(__DIR__ . '/../includes/pages/user_messages.php');
require_once realpath(__DIR__ . '/../includes/pages/user_myshifts.php');
require_once realpath(__DIR__ . '/../includes/pages/user_news.php');
require_once realpath(__DIR__ . '/../includes/pages/user_questions.php');
require_once realpath(__DIR__ . '/../includes/pages/user_settings.php');
require_once realpath(__DIR__ . '/../includes/pages/user_shifts.php');


/**
 * Init application
 */
session_start();

gettext_init();

load_auth();
