<?php
/* $Id: db_create.php,v 1.17 2002/11/19 14:09:38 rabus Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets some core libraries
 */
require('./libraries/grab_globals.lib.php');
$js_to_run = 'functions.js';
require('./header.inc.php');


/**
 * Defines the url to return to in case of error in a sql statement
 */
$err_url = 'main.php'
         . '?lang=' . $lang
         . '&amp;convcharset=' . $convcharset
         . '&amp;server=' . $server;


/**
 * Ensures the db name is valid
 */
if (PMA_MYSQL_INT_VERSION < 32306) {
    PMA_checkReservedWords($db, $err_url);
}


/**
 * Executes the db creation sql query
 */
$local_query = 'CREATE DATABASE ' . PMA_backquote($db);
$result      = PMA_mysql_query('CREATE DATABASE ' . PMA_backquote($db)) or PMA_mysqlDie('', $local_query, FALSE, $err_url);


/**
 * Displays the result and moves back to the calling page
 */
$message = $strDatabase . ' ' . htmlspecialchars($db) . ' ' . $strHasBeenCreated;
require('./db_details.php');

?>
