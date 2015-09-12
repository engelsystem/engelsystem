<?php
/**
 * This file includes all needed functions, connects to the db etc.
 */
require_once realpath(__DIR__ . '/../includes/mysqli_provider.php');

require_once realpath(__DIR__ . '/../includes/sys_auth.php');
require_once realpath(__DIR__ . '/../includes/sys_log.php');
require_once realpath(__DIR__ . '/../includes/sys_menu.php');
require_once realpath(__DIR__ . '/../includes/sys_page.php');
require_once realpath(__DIR__ . '/../includes/sys_template.php');

require_once realpath(__DIR__ . '/../includes/model/AngelType_model.php');
require_once realpath(__DIR__ . '/../includes/model/LogEntries_model.php');
require_once realpath(__DIR__ . '/../includes/model/Message_model.php');
require_once realpath(__DIR__ . '/../includes/model/NeededAngelTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/Room_model.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftEntry_model.php');
require_once realpath(__DIR__ . '/../includes/model/Shifts_model.php');
require_once realpath(__DIR__ . '/../includes/model/ShiftTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/UserAngelTypes_model.php');
require_once realpath(__DIR__ . '/../includes/model/UserGroups_model.php');
require_once realpath(__DIR__ . '/../includes/model/User_model.php');

require_once realpath(__DIR__ . '/../includes/view/AngelTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/Questions_view.php');
require_once realpath(__DIR__ . '/../includes/view/Rooms_view.php');
require_once realpath(__DIR__ . '/../includes/view/Shifts_view.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftEntry_view.php');
require_once realpath(__DIR__ . '/../includes/view/ShiftTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/UserAngelTypes_view.php');
require_once realpath(__DIR__ . '/../includes/view/User_view.php');
require_once realpath(__DIR__ . '/../includes/view/Dashboard_view.php');
require_once realpath(__DIR__ . '/../includes/view/Block_view.php');

require_once realpath(__DIR__ . '/../includes/controller/angeltypes_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/rooms_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/shifttypes_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/users_controller.php');
require_once realpath(__DIR__ . '/../includes/controller/user_angeltypes_controller.php');

require_once realpath(__DIR__ . '/../includes/helper/graph_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/internationalization_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/message_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/error_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/email_helper.php');
require_once realpath(__DIR__ . '/../includes/helper/session_helper.php');

require_once realpath(__DIR__ . '/../includes/mailer/shifts_mailer.php');

require_once realpath(__DIR__ . '/../config/config.default.php');
if (file_exists(realpath(__DIR__ . '/../config/config.php')))
  require_once realpath(__DIR__ . '/../config/config.php');

if ($maintenance_mode) {
  echo file_get_contents(__DIR__ . '/../public/maintenance.html');
  die();
}

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

require_once realpath(__DIR__ . '/../vendor/parsedown/Parsedown.php');

if (! defined('PHPUNIT_TESTSUITE')) {
  session_lifetime(24 * 60, preg_replace("/[^a-z0-9-]/", '', md5(__DIR__)));
}
session_start();

gettext_init();

sql_connect($config['host'], $config['user'], $config['pw'], $config['db']);

load_auth();
