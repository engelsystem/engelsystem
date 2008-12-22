<?PHP
/* Todo: -add if-construct with configvar for user-shirt-settings 
 *      
 *
 */
$title = "Himmel";
$header = "Deine pers&ouml;nlichen Einstellungen";
include ("../../includes/header.php");
include ("../../includes/crypt.php");

if (!IsSet($_POST["action"])) 
{
	echo Get_Text("Hallo").$_SESSION['Nick'].",<br>\n\n";
	Print_Text(13);
?>
<hr width=\"100%\">
<?PHP Print_Text("pub_einstellungen_Text_UserData");?>
<form action="./einstellungen.php" method="post">
	<input type="hidden" name="action" value="setUserData">
	<table>
		<tr>	<td><?PHP Print_Text("pub_einstellungen_Nick"); ?></td>
	  		<td><input type="text" name="eNick" size="23" value="<?PHP echo $_SESSION["Nick"]; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Name"); ?></td>
	  		<td><input type="text" name="eName" size="23" value="<?PHP echo $_SESSION['Name']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Vorname"); ?></td>
	  		<td><input type="text" name="eVorname" size="23" value="<?PHP echo $_SESSION['Vorname']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Alter"); ?></td>
	  		<td><input type="text" name="eAlter" size="3" value="<?PHP echo $_SESSION['Alter']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Telefon"); ?></td>
	  		<td><input type="text" name="eTelefon" size="40" value="<?PHP echo $_SESSION['Telefon']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Handy"); ?></td>
	  		<td><input type="text" name="eHandy" size="40" value="<?PHP echo $_SESSION['Handy']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_DECT"); ?></td>
	  		<td><input type="text" name="eDECT" size="4" value="<?PHP echo $_SESSION['DECT']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_email"); ?></td>
	  		<td><input type="text" name="eemail" size="40" value="<?PHP echo $_SESSION['email']; ?>"></td></tr>

		<tr>	<td>ICQ</td>
	  		<td><input type="text" name="eICQ" size="40" value="<?PHP echo $_SESSION['ICQ']; ?>"></td></tr>

		<tr>	<td>jabber</td>
	  		<td><input type="text" name="ejabber" size="40" value="<?PHP echo $_SESSION['jabber']; ?>"></td></tr>

		<tr>	<td><?PHP Print_Text("pub_einstellungen_Hometown"); ?></td>
	  		<td><input type="text" name="Hometown" size="40" value="<?PHP echo $_SESSION['Hometown']; ?>"></td></tr>
<?PHP
if( $_SESSION['CVS'][ "Change T_Shirt Size" ] == "Y" )
{
?>
                <tr>    <td><?PHP Print_Text("makeuser_T-Shirt"); ?></td>
			<td><select name="Sizeid">
        	         	<option <?php if($_SESSION['Size'] == S) { echo "selected"; } ?> value="S">S</option>
                		<option <?php if($_SESSION['Size'] == M) { echo "selected"; } ?> value="M">M</option>
                        	<option <?php if($_SESSION['Size'] == L) { echo "selected"; } ?> value="L">L</option>
                        	<option <?php if($_SESSION['Size'] == XL) { echo "selected"; } ?> value="XL">XL</option>
                        	<option <?php if($_SESSION['Size'] == XXL) { echo "selected"; } ?> value="XXL">XXL</option>
                        	<option <?php if($_SESSION['Size'] == XXXL) { echo "selected"; } ?> value="XXXL">XXXL</option>
	                </select></td></tr>
<?PHP
}
?>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<br>


<hr width=\"100%\">
<?PHP Print_Text(14);?>
<form action="./einstellungen.php" method="post">
	<input type="hidden" name="action" value="set">
	<table>
	  <tr><td><?PHP Print_Text(15); ?></td><td><input type="password" name="old" size="20"></td></tr>
	  <tr><td><?PHP Print_Text(16); ?></td><td><input type="password" name="new1" size="20"></td></tr>
	  <tr><td><?PHP Print_Text(17); ?></td><td><input type="password" name="new2" size="20"></td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<br>


<hr width="100%">
<br>
<?PHP Print_Text(18); ?>
<form action="./einstellungen.php" method="post">
        <input type="hidden" name="action" value="colour">
	<table>
	   <tr><td><?PHP Print_Text(19); ?></td>
	   <td>
		<select name="colourid">
			<option <?php if($_SESSION['color'] == 1) { echo "selected"; } ?> value="1">Standard-Style</option>
			<option <?php if($_SESSION['color'] == 2) { echo "selected"; } ?> value="2">Rot/Gelber Style</option>
			<option <?php if($_SESSION['color'] == 3) { echo "selected"; } ?> value="3">Club-Mate Style</option>
			<option <?php if($_SESSION['color'] == 5) { echo "selected"; } ?> value="5">Debian Style</option>
			<option <?php if($_SESSION['color'] == 6) { echo "selected"; } ?> value="6">c-base Style</option>
			<option <?php if($_SESSION['color'] == 7) { echo "selected"; } ?> value="7">Blau/Gelber Style </option>
			<option <?php if($_SESSION['color'] == 8) { echo "selected"; } ?> value="8">Pastel Style</option>
			<option <?php if($_SESSION['color'] == 4) { echo "selected"; } ?> value="4">Test Style</option>
			<option <?php if($_SESSION['color'] == 9) { echo "selected"; } ?> value="9">Test Style 21c3 </option>
		</select>
	   </td></tr>
	   <tr><td>Menu</td>
	   	<td><input type="radio" name="eMenu" value="L"<?PHP 
			if ($_SESSION['Menu']=='L') echo " checked"; ?>>L
		    <input type="radio" name="eMenu" value="R"<?PHP
			if ($_SESSION['Menu']=='R') echo " checked"; ?>>R
	   	</td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<br>
<hr width="100%">
<br>
<?PHP Print_Text(20); ?>
<form action="./einstellungen.php" method="post">
        <input type="hidden" name="action" value="sprache">
	<table>
	   <tr><td><?PHP Print_Text(21); ?></td>
	   <td>
		<select name="language">
		   <option value="DE" <?php if($_SESSION['Sprache'] == 'DE') { echo "selected"; } ?>>Deutsch</option>
		   <option value="EN" <?php if($_SESSION['Sprache'] == 'EN') { echo "selected"; } ?>>English</option>
<?PHP /*		   <option value="NL" <?php if($_SESSION['Sprache'] == 'NL') { echo "selected"; } ?>>Dutch</option>  */?>
		</select>
	   </td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<?PHP 

	
	if( get_cfg_var("file_uploads"))
	{
		echo "<br>\n<hr width=\"100%\">\n<br>\n\n";
		echo Get_Text('pub_einstellungen_PictureUpload')."<br>";
		echo "<form action=\"./einstellungen.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
		echo "\t<input type=\"hidden\" name=\"action\" value=\"sendPicture\">\n";
		echo "\t<input name=\"file\" type=\"file\" size=\"50\" maxlength=\"". get_cfg_var("post_max_size"). "\">\n";
		echo "\t(max ". get_cfg_var("post_max_size"). "Byte)<br>\n";
		echo "\t<input type=\"submit\" value=\"". Get_Text("upload"),"\">\n";
		echo "</form>\n";
	}

	switch( GetPicturShow( $_SESSION['UID']))
	{
		case 'Y':
			echo Get_Text('pub_einstellungen_PictureShow'). "<br>";
			echo displayPictur($_SESSION['UID'], 0);
			echo "<form action=\"./einstellungen.php\" method=\"post\">\n";
			echo "\t<input type=\"hidden\" name=\"action\" value=\"delPicture\">\n";
			echo "\t<input type=\"submit\" value=\"". Get_Text("delete"),"\">\n";
			echo "</form>\n";
			break;
		case 'N':
			echo Get_Text('pub_einstellungen_PictureNoShow'). "<br>";
			echo displayPictur($_SESSION['UID'], 0);
			echo "<form action=\"./einstellungen.php\" method=\"post\">\n";
			echo "\t<input type=\"hidden\" name=\"action\" value=\"delPicture\">\n";
			echo "\t<input type=\"submit\" value=\"". Get_Text("delete"),"\">\n";
			echo "</form>\n";
			echo "<br>\n<hr width=\"100%\">\n<br>\n\n";
		case '':	
			echo "<br>\n<hr width=\"100%\">\n<br>\n\n";
			echo Get_Text(22). "<br>"; 
			echo "\n<form action=\"./einstellungen.php\" method=\"post\">\n";
			echo "\t<input type=\"hidden\" name=\"action\" value=\"avatar\">\n";
			echo "\t<table>\n";
			echo "\t\t<tr>\n\t\t\t<td>". Get_Text(23). "<br></td>\n\t\t</tr>\n";
			echo "\t\t<tr>\n";
			echo "\t\t\t<td>\n";
		    	echo "\t\t\t\t<select name=\"eAvatar\" onChange=\"document.avatar.src = '../pic/avatar/avatar' + this.value  + '.gif'\"".
		    		"onKeyup=\"document.avatar.src = '../pic/avatar/avatar' + this.value  + '.gif'\">\n"; 
			for ($i=1; file_exists("../pic/avatar/avatar$i.gif"); $i++ )
		    		echo "\t\t\t\t\t<option value=\"$i\"". ($_SESSION['Avatar'] == $i ? " selected":""). ">avatar$i</option>\n";
		    	echo "\t\t\t\t</select>&nbsp;&nbsp;\n";
			echo "\t\t\t\t<img src=\"../pic/avatar/avatar". $_SESSION['Avatar']. ".gif\" name=\"avatar\" border=\"0\" align=\"top\">\n";
			echo "\t\t\t</td>\n\t\t</tr>\n";
			echo "\t</table>\n";
			echo "\t<input type=\"submit\" value=\"". Get_Text("save"),"\">\n";
			echo "</form>\n";
			break;
	} //CASE

} else {

switch ($_POST["action"]) {

case 'set':
  if ($_POST["new1"]==$_POST["new2"]){
	Print_Text(25); 
	$sql = "SELECT * FROM `User` WHERE `UID`='".$_SESSION['UID']. "'";
	$Erg = mysql_query($sql, $con);
	if (PassCrypt($_POST["old"])==mysql_result($Erg, 0, "Passwort")) {
		Print_Text(26);
		Print_Text(27);
		$usql = "UPDATE `User` SET `Passwort`='". PassCrypt($_POST["new1"]). "' ".
                " WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1";
		$Erg = mysql_query($usql, $con);
		if ($Erg==1) {
			Print_Text(28);
		} else {
			Print_Text(29);
		}		
	} else {
		Print_Text(30);
	}
  } else {
	Print_Text(31);
  }
	break;

case 'colour':

	$chsql="UPDATE `User` SET ".
		"`color`= '". $_POST["colourid"]. "', ".
		"`Menu`= '". $_POST["eMenu"]. "' ".
		"WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1";
	$Erg = mysql_query($chsql, $con);
	echo mysql_error($con);
	$_SESSION['color']=$_POST["colourid"];
	$_SESSION['Menu']=$_POST["eMenu"];
	if ($Erg==1) {
		Print_Text(32);
	} else {
		Print_Text(29);
	}
	break;

case 'sprache':

	$chsql="UPDATE `User` SET `Sprache` = '". $_POST["language"]. "' WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1";
	$Erg = mysql_query($chsql, $con);
	$_SESSION['Sprache']=$_POST["language"];
	if ($Erg==1) {
		Print_Text(33);
	} else {
		Print_Text(29);
	}
	break;

case 'avatar':
	$chsql="UPDATE `User` SET `Avatar`='". $_POST["eAvatar"]. "' WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1";
        $Erg = mysql_query($chsql, $con);
	$_SESSION['Avatar']=$_POST["eAvatar"];
	if ($Erg==1)
		Print_Text(34);
        else
		Print_Text(29);
        break;

case 'setUserData':
	if( $_SESSION['CVS'][ "Change T_Shirt Size" ] == "Y" )
	{
		$chsql= "UPDATE `User` SET ".
			"`Nick`='".     $_POST["eNick"].	"', `Name`='".   $_POST["eName"].  "', ".
			"`Vorname`='".  $_POST["eVorname"].	"', `Alter`='".  $_POST["eAlter"]. "', ".
			"`Telefon`='".  $_POST["eTelefon"].	"', `Handy`='".  $_POST["eHandy"]. "', ".
			"`DECT`='".     $_POST["eDECT"]. 	"', `email`='".  $_POST["eemail"]. "', ".
			"`ICQ`='".      $_POST["eICQ"].		"', `jabber`='". $_POST["ejabber"]."', ".
			"`Hometown`='". $_POST["Hometown"].	"', `Size`='".   $_POST["Sizeid"]. "' ".
			"WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1;";
	}
	else
	{
		$chsql= "UPDATE `User` SET ".
			"`Nick`='".     $_POST["eNick"].	"', `Name`='".   $_POST["eName"].  "', ".
			"`Vorname`='".  $_POST["eVorname"].	"', `Alter`='".  $_POST["eAlter"]. "', ".
			"`Telefon`='".  $_POST["eTelefon"].	"', `Handy`='".  $_POST["eHandy"]. "', ".
			"`DECT`='".     $_POST["eDECT"]. 	"', `email`='".  $_POST["eemail"]. "', ".
			"`ICQ`='".      $_POST["eICQ"].		"', `jabber`='". $_POST["ejabber"]."', ".
			"`Hometown`='". $_POST["Hometown"].	"' ".
			"WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1;";
	}
        $Erg = mysql_query($chsql, $con);

	if ($Erg==1) 
	{
		$_SESSION['Nick'] = $_POST["eNick"];
		$_SESSION['Name'] = $_POST["eName"];
		$_SESSION['Vorname'] = $_POST["eVorname"];
		$_SESSION['Alter'] = $_POST["eAlter"];
		$_SESSION['Telefon'] = $_POST["eTelefon"];
		$_SESSION['Handy'] = $_POST["eHandy"];
		$_SESSION['DECT'] = $_POST["eDECT"];
		$_SESSION['email'] = $_POST["eemail"];
		$_SESSION['ICQ'] = $_POST["eICQ"];
		$_SESSION['jabber'] = $_POST["ejabber"];
		$_SESSION['Hometown'] = $_POST["Hometown"];
		if( $_SESSION['CVS'][ "Change T_Shirt Size" ] == "Y" )
		{
			$_SESSION['Size']=$_POST["Sizeid"];		
		}
		else if( $_SESSION['Size'] != $_POST["Sizeid"]) 
		{
			array_push($error_messages, "einstellungen.php, change t-shirt size not allowed\n");
		}

	
		Print_Text("pub_einstellungen_UserDateSaved");
        } 
	else
	{
		Print_Text(29);
		echo mysql_error( $con);
	}
	break;

case 'sendPicture':
	if( $_FILES["file"]["size"] > 0)
	{
	  if( ($_FILES["file"]["type"] == "image/jpeg") || 
	      ($_FILES["file"]["type"] == "image/png")  ||
	      ($_FILES["file"]["type"] == "image/gif")  )
	  {		
		$data = addslashes(fread(fopen($_FILES["file"]["tmp_name"], "r"), filesize($_FILES["file"]["tmp_name"])));

		if( GetPicturShow( $_SESSION['UID']) == "")
			$SQL = "INSERT INTO `UserPicture` ".
				"( `UID`,`Bild`, `ContentType`, `show`) ".
				"VALUES ('". $_SESSION['UID']. "', '$data', '". $_FILES["file"]["type"]. "', 'N')";
		else
			$SQL = "UPDATE `UserPicture` SET ".
				"`Bild`='$data', ".
				"`ContentType`='". $_FILES["file"]["type"]. "', ".
				"`show`='N' ".
				"WHERE `UID`='". $_SESSION['UID']. "'";
		
		$res = mysql_query( $SQL, $con);
		if( $res)
			Print_Text("pub_einstellungen_send_OK");
		else
			Print_Text("pub_einstellungen_send_KO");
	
		echo "<h6>('" . $_FILES["file"]["name"] . "', MIME-Type: " . $_FILES["file"]["type"]. ", " . $_FILES["file"]["size"]. " Byte)</h6>";
	  }
	  else
		Print_Text("pub_einstellungen_send_KO");	
	}
	else
		Print_Text("pub_einstellungen_send_KO");	
	break;

case 'delPicture':
	$chsql="DELETE FROM `UserPicture` WHERE `UID`='". $_SESSION['UID']. "' LIMIT 1";
        $Erg = mysql_query($chsql, $con);
	if ($Erg==1)
		Print_Text("pub_einstellungen_del_OK");
        else
		Print_Text("pub_einstellungen_del_KO");
	Break;
}
}
include ("../../includes/footer.php");
?>
