<?php
/* $Id: tbl_properties_links.php,v 1.29 2002/10/23 04:17:43 robbat2 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:


/**
 * Sets error reporting level
 */
error_reporting(E_ALL);


/**
 * Count amount of navigation tabs
 */
$db_details_links_count_tabs = 0;


/**
 * Prepares links
 */
if ($table_info_num_rows > 0) {
    $lnk2    = 'sql.php';
    $arg2    = $url_query
             . '&amp;sql_query=' . urlencode('SELECT * FROM ' . PMA_backquote($table))
             . '&amp;pos=0';
    $lnk4    = 'tbl_select.php';
    $arg4    = $url_query;
    $ln6_stt = (PMA_MYSQL_INT_VERSION >= 40000)
             ? 'TRUNCATE TABLE '
             : 'DELETE FROM ';
    $lnk6    = 'sql.php';
    $arg6    = $url_query . '&amp;sql_query='
             . urlencode($ln6_stt . PMA_backquote($table))
             .  '&amp;zero_rows='
             .  urlencode(sprintf($strTableHasBeenEmptied, htmlspecialchars($table)));
    $att6    = 'class="drop" onclick="return confirmLink(this, \'' . $ln6_stt . PMA_jsFormat($table) . '\')"';
} else {
    $lnk2    = '';
    $arg2    = '';
    $lnk4    = '';
    $arg4    = '';
    $lnk6    = '';
    $arg6    = '';
    $att6    = '';
}

// The 'back' is supposed to be set to the current sub-page. This is necessary
// when you have js deactivated, you click on Drop, then click cancel, and want
// to get back to the same sub-page.
$arg7 = ereg_replace('tbl_properties[^.]*.php$', 'db_details.php', $url_query) . '&amp;reload=1&amp;sql_query=' . urlencode('DROP TABLE ' . PMA_backquote($table) ) . '&amp;zero_rows=' . urlencode(sprintf($strTableHasBeenDropped, htmlspecialchars($table)));
$att7 = 'class="drop" onclick="return confirmLink(this, \'DROP TABLE ' . PMA_jsFormat($table) . '\')"';


/**
 * Displays links
 */
?>
<table border="0" cellspacing="0" cellpadding="3" width="100%" class="tabs">
    <tr>
        <td width="8">&nbsp;</td>
<?php
echo PMA_printTab($strStructure, 'tbl_properties_structure.php', $url_query);
echo PMA_printTab($strBrowse, $lnk2, $arg2);
echo PMA_printTab($strSQL, 'tbl_properties.php', $url_query);
echo PMA_printTab($strSelect, $lnk4, $arg4);
echo PMA_printTab($strInsert, 'tbl_change.php', $url_query);
echo PMA_printTab($strExport, 'tbl_properties_export.php', $url_query);
echo PMA_printTab($strOperations, 'tbl_properties_operations.php', $url_query);
if (PMA_MYSQL_INT_VERSION >= 32322) {
    echo PMA_printTab($strOptions, 'tbl_properties_options.php', $url_query);
}
echo PMA_printTab($strEmpty, $lnk6, $arg6, $att6);
echo PMA_printTab($strDrop, 'sql.php', $arg7, $att7);
echo "\n";
?>
    </tr>
</table>
<br />

