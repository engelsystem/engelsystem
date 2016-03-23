<?php

require_once realpath(__DIR__ . '/../includes/mysqli_provider.php');
require_once realpath(__DIR__ . '/../config/config.default.php');
if(file_exists(realpath(__DIR__ . '/../config/config.php')))
  require_once realpath(__DIR__ . '/../config/config.php');
sql_connect($config['host'], $config['user'], $config['pw'], $config['db']);

error_reporting(E_ALL | E_NOTICE);

define('UPDATE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'update.d');

function _datetime_to_int($table, $col) {
    $table = sql_escape($table);
    $col = sql_escape($col);

    $res = sql_select("DESCRIBE `" . $table . "` `" . $col . "`");
    if($res[0]['Type'] == "datetime") {
        sql_query("ALTER TABLE `" . $table . "` ADD `" . $col . "_new` INT NOT NULL AFTER `" . $col . "`");
        # XXX: we don't consider indexes etc. here and just copy the data!
        sql_query("UPDATE `" . $table . "` SET `" . $col . "_new` = UNIX_TIMESTAMP(`" . $col . "`)");
        sql_query("ALTER TABLE `" . $table . "` DROP `" . $col . "`, CHANGE `" . $col . "_new` `" . $col . "` INT NOT NULL");

        global $applied;
        $applied = true;
        return true;
    } else {
        return false;
    }
}

function _rename_table($old, $new) {
    $old = sql_escape($old);
    $new = sql_escape($new);

    if(sql_num_query("SHOW TABLES LIKE '" . $new . "'") === 0
            && sql_num_query("SHOW TABLES LIKE '" . $old . "'") === 1) {
        sql_query("RENAME TABLE `" . $old . "` TO `" . $new . "`");

        global $applied;
        $applied = true;
        return true;
    } else {
        return false;
    }
}

function _add_index($table, $cols, $type = "INDEX") {
    $table = sql_escape($table);
    $cols = array_map('sql_escape', $cols);
    $type = sql_escape($type);

    if(sql_num_query("SHOW INDEX FROM `" . $table . "` WHERE `Key_name` = '" . $cols[0] . "'") == 0) {
        sql_query("ALTER TABLE `" . $table . "` ADD " . $type . " (`" . implode($cols, '`,`') . "`)");

        global $applied;
        $applied = true;
        return true;
    } else {
        return false;
    }
}

$updates = scandir(UPDATE_DIR);
foreach($updates as $update) {
    if(substr($update, -4) == '.php') {
        $applied = false;
        require_once( UPDATE_DIR . DIRECTORY_SEPARATOR . $update);
        if($applied)
            echo "Successfully applied " . $update . " (at least partially).\n";
    }
}
?>
