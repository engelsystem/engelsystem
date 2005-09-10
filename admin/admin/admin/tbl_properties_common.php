<?php
/* $Id: tbl_properties_common.php,v 1.9 2002/10/23 04:17:43 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets some core libraries
 */
if (!defined('PMA_GRAB_GLOBALS_INCLUDED')) {
    include('./libraries/grab_globals.lib.php');
}
if (!defined('PMA_COMMON_LIB_INCLUDED')) {
    include('./libraries/common.lib.php');
}
if (!defined('PMA_BOOKMARK_LIB_INCLUDED')) {
    include('./libraries/bookmark.lib.php');
}


/**
 * Defines the urls to return to in case of error in a sql statement
 */
$err_url_0 = $cfg['DefaultTabDatabase']
           . '?lang=' . $lang
           . '&amp;convcharset=' . $convcharset
           . '&amp;server=' . $server
           . '&amp;db=' . urlencode($db);
$err_url   = $cfg['DefaultTabTable']
           . '?lang=' . $lang
           . '&amp;convcharset=' . $convcharset
           . '&amp;server=' . $server
           . '&amp;db=' . urlencode($db)
           . '&amp;table=' . urlencode($table);


/**
 * Ensures the database and the table exist (else move to the "parent" script)
 */
require('./libraries/db_table_exists.lib.php');


/**
 * Displays headers
 */
if (!isset($message)) {
    $js_to_run = 'functions.js';
    include('./header.inc.php');
} else {
    PMA_showMessage($message);
}


/**
 * Set parameters for links
 */
$url_query = 'lang=' . $lang
           . '&amp;convcharset=' . $convcharset
           . '&amp;server=' . $server
           . '&amp;db=' . urlencode($db)
           . '&amp;table=' . urlencode($table);

?>
