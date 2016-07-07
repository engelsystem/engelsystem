<?php
function admin_export_title() {
  return _("Import and Export User data ");
}

function admin_export() {
  if(isset($_REQUEST['download'])){
    $filename = tempnam('/tmp', '.csv'); //  Temporary File Name
    $temp = sql_query("CREATE TEMPORARY TABLE `temp_tb` SELECT * FROM `User`");
	  $drop = sql_query("ALTER TABLE `temp_tb` DROP `Passwort`");
	  $drop2 = ("ALTER TABLE `temp_tb` DROP `password_recovery_token`");
	  $headings = sql_select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'User' ");
	  $head = "";
	  foreach($headings as $heading) {
	    if ((strcmp($heading["COLUMN_NAME"],'Passwort') && strcmp($heading["COLUMN_NAME"],'password_recovery_token')) !=0)
	      $head .= $heading["COLUMN_NAME"] . " ";
	  }
  	$final = explode(" ", $head);
  	$results = sql_select("SELECT * FROM `temp_tb`");
	  $fp = fopen("$filename", "w+") or die("Error Occurred");
	  fputcsv($fp, $final, "\t");
	  foreach($results as $result) {
		  fputcsv($fp, $result, "\t");
	  }
	  $fp = @fopen($filename, 'rb+');
    if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
      header('Content-Type: application/csv');
      header('Content-Disposition: attachment; filename=export_users_data.csv');
      header('Expires: 0');
		  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header("Content-Transfer-Encoding: binary");
      header('Pragma: public');
		  header("Content-Length: ".filesize($filename));
	  }
	  else {
      header('Content-Type: application/csv');
		  header('Content-Disposition: attachment; filename=export_users_data.csv');
		  header("Content-Transfer-Encoding: binary");
		  header('Expires: 0');
		  header('Pragma: no-cache');
		  header("Content-Length: ".filesize($filename));
	  }
	  fpassthru($fp);
	  fclose($fp);
 }
 return page_with_title(admin_export_title(), array(
   $msg,
   msg(),
   div('well well-sm text-center', [
     _('Export User Database')
   ]).div('row', array(
          div('col-md-12', array(
              form(array(
                form_info('', _("This will export user data.Press export button to download the user data.")),
                form_submit('download', _("Export"))
              ))
          ))
      )).div('well well-sm text-center', [
            _('Import User Database')
        ]).div('row', array(
          div('col-md-12', array(
              form(array(
                form_info('', _("This will import user data.Press Import button to download the user data.")),
                form_submit('upload', _("Import"))
              ))
          ))
      ))
  ));
}
?>
