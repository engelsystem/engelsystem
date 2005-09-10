<?php
/* $Id: tbl_properties.php,v 1.181 2002/10/23 04:17:43 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Runs common work
 */
require('./tbl_properties_common.php');
$err_url   = 'tbl_properties.php' . $err_url;
$url_query .= '&amp;goto=tbl_properties.php&amp;back=tbl_properties.php';

/**
 * Top menu
 */
require('./tbl_properties_table_info.php');

?>
<ul>

<!-- TABLE WORK -->
<?php
/**
 * Query box, bookmark, insert data from textfile
 */
$goto = 'tbl_properties.php';
require('./tbl_query_box.php');

?>
</ul>

<?php

/**
 * Displays the footer
 */
echo "\n";
require('./footer.inc.php');
?>
