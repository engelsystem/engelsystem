<?php
/* $Id: chk_rel.php,v 1.3 2002/10/23 04:17:42 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Gets some core libraries
 */
require('./libraries/grab_globals.lib.php');
require('./libraries/common.lib.php');
require('./db_details_common.php');
require('./libraries/relation.lib.php');


/**
 * Gets the relation settings
 */
$cfgRelation = PMA_getRelationsParam(TRUE);


/**
 * Displays the footer
 */
echo "\n";
require('./footer.inc.php');
?>
