<?php
function admin_export_title() {
  return _("Import and Export User data ");
}

function admin_export() {
  if(isset($_REQUEST['download'])){
    $filename = tempnam('/tmp', '.csv'); //  Temporary File Name
    sql_query("CREATE TEMPORARY TABLE `temp_tb` SELECT * FROM `User`");
	  sql_query("ALTER TABLE `temp_tb` DROP `Passwort`");
	  sql_query("ALTER TABLE `temp_tb` DROP `password_recovery_token`");
	  $headings = sql_select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'User' ");
	  $head = "";
	  foreach($headings as $heading) {
	    if ((strcmp($heading["COLUMN_NAME"],'Passwort') && strcmp($heading["COLUMN_NAME"],'password_recovery_token')) !=0 )
	      $head .= $heading["COLUMN_NAME"] . " ";
	  }
  	$final = explode(" ", $head);
  	$results = sql_select("SELECT * FROM `temp_tb`");
	  $filep = fopen("$filename", "w+");
	  fputcsv($filep, $final, "\t");
	  foreach($results as $result) {
		  fputcsv($filep, $result, "\t");
	  }
	  $filep = @fopen($filename, 'rb+');
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
	  fpassthru($filep);
	  fclose($filep);
 }

  if (isset($_REQUEST['upload'])) {
    $ok = true;
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");
    if ($file == NULL) {
      error(_('Please select a file to import'));
      redirect(page_link_to('admin_export'));
    }
    else{
      while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
        {
          $nick = $filesop[0];
          $prename = $filesop[1];
          $lastname = $filesop[2];
          $mail = $filesop[3];
          $current_city = $filesop[4];
          $password = $filesop[5];
          $planned_arrival_date = $filesop[6];
          $timezone = $filesop[7];
          $mobile = $filesop[8];
          $tel = $filesop[9];
          $age = $filesop[10];
          $hometown = $filesop[11];
          $organization = $filesop[12];
          $organization_web = $filesop[13];
          $facebook = $filesop[14];
          $github = $filesop[15];
          $twitter = $filesop[16];
          $jabber = $filesop[17];
          $dect = $filesop[18];
          $tshirt_size = $filesop[19];
          $email_shiftinfo = false;
          $selected_angel_types = array();
          $password_hash = "";

        if (strlen(User_validate_Nick($nick)) > 1) {
          $nick = User_validate_Nick($nick);
          if (sql_num_query("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "' LIMIT 1") > 0) {
            $ok = false;
            $msg .= error(sprintf(_("Your nick &quot;%s&quot; already exists."), $nick), true);
          }
        } else {
          $ok = false;
          $msg .= error(sprintf(_("Your nick &quot;%s&quot; is too short (min. 2 characters)."),User_validate_Nick($_REQUEST['nick'])), true);
        }

        if ( strlen($mail) && preg_match("/^[a-z0-9._+-]{1,64}@(?:[a-z0-9-]{1,63}\.){1,125}[a-z]{2,63}$/", $mail) > 0) {
          if (! check_email($mail)) {
            $ok = false;
            $msg .= error(_("E-mail address is not correct."), true);
          }
        }

        if (sql_num_query("SELECT * FROM `User` WHERE `email`='" . sql_escape($mail) . "' LIMIT 1")  > 0) {
          $ok = false;
          $msg .= error(sprintf(_("Your E-mail &quot;%s&quot; already exists.<a href=%s>Forgot password?</a>"), $mail,page_link_to_absolute('user_password_recovery')), true);
        } else {
          $ok = false;
          $msg .= error(_("Please enter your correct e-mail (in lowercase)."), true);
        }

        if (strlen($password) >= MIN_PASSWORD_LENGTH) {
            $ok = true;
          } else {
            $ok = false;
            $msg .= error(sprintf(_("Your password is too short (please use at least %s characters)."), MIN_PASSWORD_LENGTH), true);
        }
        if ($ok) {
          $sql = sql_query("
            INSERT INTO `User` SET
            `Nick`='" . sql_escape($nick) . "',
            `Vorname`='" . sql_escape($prename) . "',
            `Name`='" . sql_escape($lastname) . "',
            `Alter`='" . sql_escape($age) . "',
            `Telefon`='" . sql_escape($tel) . "',
            `DECT`='" . sql_escape($dect) . "',
            `Handy`='" . sql_escape($mobile) . "',
            `email`='" . sql_escape($mail) . "',
            `email_shiftinfo`=" . sql_bool($email_shiftinfo) . ",
            `jabber`='" . sql_escape($jabber) . "',
            `Size`='" . sql_escape($tshirt_size) . "',
            `Passwort`='" . sql_escape($password_hash) . "',
            `kommentar`='" . sql_escape($comment) . "',
            `Hometown`='" . sql_escape($hometown) . "',
            `CreateDate`= NOW(),
            `Sprache`='" . sql_escape($_SESSION["locale"]) . "',
            `arrival_date`= NULL,
            `twitter`='" . sql_escape($twitter) . "',
            `facebook`='" . sql_escape($facebook) . "',
            `github`='" . sql_escape($github) . "',
            `organization`='" . sql_escape($organization) . "',
            `current_city`='" . sql_escape($current_city) . "',
            `organization_web`='" . sql_escape($organization_web) . "',
            `timezone`='" . sql_escape($timezone) . "',
            `planned_arrival_date`='" . sql_escape($planned_arrival_date) . "'");

          $user_id = sql_id();
          sql_query("INSERT INTO `UserGroups` SET `uid`='" . sql_escape($user_id) . "', `group_id`=-2");
          set_password($user_id, $_REQUEST['password']);
          engelsystem_log("User " . User_Nick_render(User($user_id)) . " signed up as: " . join(", ", $user_angel_types_info));
        }
      }

      if ($sql) {
        success(_("You database has imported successfully!"));
        redirect(page_link_to('admin_export'));
      } else {
        error(_('Sorry! There is some problem in the import file.'));
        redirect(page_link_to('admin_export'));
        }
    }
  }

 return page_with_title(admin_export_title(), array(
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
                form_info('', _("This will import user data.Press Import button to upload the user data.")),
                form_file('csv_file', _("Import user data from a csv file")),
                form_submit('upload', _("Import"))
              ))
          ))
      ))
  ));
}
?>
