<?php
/* $Id: phpinfo.php,v 1.9 2002/10/23 04:17:43 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets core libraries and defines some variables
 */
require('./libraries/grab_globals.lib.php');
require('./libraries/common.lib.php');


/**
 * Displays PHP information
 */
$is_superuser = @PMA_mysql_query('USE mysql', $userlink);
if ($is_superuser || $cfg['ShowPhpInfo']) {
    phpinfo();
}
?>
