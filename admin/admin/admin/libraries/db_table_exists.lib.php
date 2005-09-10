<?php
/* $Id: db_table_exists.lib.php,v 1.3 2002/10/23 04:17:26 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Ensure the database and the table exist (else move to the "parent" script)
 * and display headers
 */
if (!isset($is_db) || !$is_db) {
    // Not a valid db name -> back to the welcome page
    if (!empty($db)) {
        $is_db = @PMA_mysql_select_db($db);
    }
    if (empty($db) || !$is_db) {
        header('Location: ' . $cfg['PmaAbsoluteUri'] . 'main.php?lang=' . $lang . '&convcharset=' . $convcharset . '&server=' . $server . (isset($message) ? '&message=' . urlencode($message) : '') . '&reload=1');
        exit();
    }
} // end if (ensures db exists)
if (!isset($is_table) || !$is_table) {
    // Not a valid table name -> back to the db_details.php
    if (!empty($table)) {
        $is_table = @PMA_mysql_query('SHOW TABLES LIKE \'' . PMA_sqlAddslashes($table, TRUE) . '\'');
    }
    if (empty($table)
        || !($is_table && @mysql_numrows($is_table))) {
        header('Location: ' . $cfg['PmaAbsoluteUri'] . 'db_details.php?lang=' . $lang . '&convcharset=' . $convcharset . '&server=' . $server . '&db=' . urlencode($db) . (isset($message) ? '&message=' . urlencode($message) : '') . '&reload=1');
        exit();
    } else if (isset($is_table)) {
        mysql_free_result($is_table);
    }
} // end if (ensures table exists)
?>
