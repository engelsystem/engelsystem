<?
$title = "Himmel";
$header = "Deine pers&ouml;nlichen Einstellungen";
include ("./inc/header.php");
include ("./inc/crypt.php");

if (!IsSet($_POST["action"])) 
{
	echo Get_Text("Hallo").$_SESSION['Nick'].",<br>\n\n";
	Print_Text(13);
?>
<hr width=\"100%\">
<? Print_Text("pub_einstellungen_Text_UserData");?>
<form action="./einstellungen.php" method="post">
	<input type="hidden" name="action" value="setUserData">
	<table>
		<tr>	<td><? Print_Text("pub_einstellungen_Nick"); ?></td>
	  		<td><input type="text" name="eNick" size="23" value="<? echo $_SESSION["Nick"]; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Name"); ?></td>
	  		<td><input type="text" name="eName" size="23" value="<? echo $_SESSION['Name']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Vorname"); ?></td>
	  		<td><input type="text" name="eVorname" size="23" value="<? echo $_SESSION['Vorname']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Alter"); ?></td>
	  		<td><input type="text" name="eAlter" size="3" value="<? echo $_SESSION['Alter']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Telefon"); ?></td>
	  		<td><input type="text" name="eTelefon" size="40" value="<? echo $_SESSION['Telefon']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Handy"); ?></td>
	  		<td><input type="text" name="eHandy" size="40" value="<? echo $_SESSION['Handy']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_DECT"); ?></td>
	  		<td><input type="text" name="eDECT" size="4" value="<? echo $_SESSION['DECT']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_email"); ?></td>
	  		<td><input type="text" name="eemail" size="40" value="<? echo $_SESSION['email']; ?>"></td></tr>
		<tr>	<td><? Print_Text("pub_einstellungen_Hometown"); ?></td>
	  		<td><input type="text" name="Hometown" size="40" value="<? echo $_SESSION['Hometown']; ?>"></td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<br>


<hr width=\"100%\">
<? Print_Text(14);?>
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
	   	<td><input type="radio" name="eMenu" value="L"<? 
			if ($_SESSION['Menu']=='L') echo " checked"; ?>>L
		    <input type="radio" name="eMenu" value="R"<?
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
		   <option value="NL" <?php if($_SESSION['Sprache'] == 'NL') { echo "selected"; } ?>>Dutch</option>
		</select>
	   </td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<br>
<hr width="100%">
<br>
<?PHP Print_Text(22); ?>
<form action="./einstellungen.php" method="post">
        <input type="hidden" name="action" value="avatar">
	<table>
		<tr>
			<td><?PHP Print_Text(23); ?><br></td>
			<td>
			</td>
		</tr>
		<tr>
			<td>


<?

// Anzahl der installierten Avatars

//$ANZ_AVATAR= shell_exec("ls ".$_SERVER["DOCUMENT_ROOT"].$ENGEL_ROOT."inc/avatar/ | wc -l");
$ANZ_AVATAR= shell_exec("ls inc/avatar/ | wc -l");
	    ?> 
	    
	    <select name="eAvatar" onChange="document.avatar.src = './inc/avatar/avatar' + this.value  + '.gif'"
	    			   onKeyup= "document.avatar.src = './inc/avatar/avatar' + this.value  + '.gif'"> 
	    <?php
	    for ($i=1; $i <= $ANZ_AVATAR; $i++ )
	    {
	    	echo "\t\t\t\t<option value=\"$i\"";
		if ($_SESSION['Avatar'] == $i) { echo " selected"; }
	    	echo ">avatar$i</option>\n";
	    }
	    echo "\n";
	    ?>
	    </select>&nbsp;&nbsp;
	    <img src="./inc/avatar/avatar<?php echo $_SESSION['Avatar']; ?>.gif" name="avatar" border="0" alt="" align="top">
	  </td></tr>
	</table>
	<input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>


<?

} else {

switch ($_POST["action"]) {

case 'set':
  if ($_POST["new1"]==$_POST["new2"]){
	Print_Text(25); 
	$sql = "select * from User where UID=".$_SESSION['UID'];
	$Erg = mysql_query($sql, $con);
	if (PassCrypt($_POST["old"])==mysql_result($Erg, 0, "Passwort")) {
		Print_Text(26);
		Print_Text(27);
		$usql = "update User set Passwort='".PassCrypt($_POST["new1"])."' ".
			"where UID=".$_SESSION['UID']." limit 1";
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

	$chsql="Update User set ".
		"`color` = \"". $_POST["colourid"]. "\", ".
		"`Menu`= \"". $_POST["eMenu"]. "\" ".
		"where UID = \"".$_SESSION['UID']."\" limit 1";
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

	$chsql="Update User set Sprache = \"". $_POST["language"]. "\" where UID = \"".$_SESSION['UID']."\" limit 1";
	$Erg = mysql_query($chsql, $con);
	$_SESSION['Sprache']=$_POST["language"];
	if ($Erg==1) {
		Print_Text(33);
	} else {
		Print_Text(29);
	}
	break;

case 'avatar':
	$chsql="Update User set Avatar = \"". $_POST["eAvatar"]. "\" where UID = \"". $_SESSION['UID']. "\" limit 1";
        $Erg = mysql_query($chsql, $con);
	$_SESSION['Avatar']=$_POST["eAvatar"];
	if ($Erg==1) {
		Print_Text(34);
        } else {
		Print_Text(29);
	}
        break;

case 'setUserData':
	$chsql= "UPDATE User SET ".
		"`Nick`='". $_POST["eNick"]. "', `Name`='". $_POST["eName"]. "', ".
		"`Vorname`='". $_POST["eVorname"]. "', `Alter`='". $_POST["eAlter"]. "', ".
		"`Telefon`='". $_POST["eTelefon"]. "', `Handy`='". $_POST["eHandy"]. "', ".
		"`DECT`='". $_POST["eDECT"]. "', `email`='". $_POST["eemail"]. "', `Hometown`='". $_POST["Hometown"]. "' ".
		"WHERE UID='". $_SESSION['UID']. "' LIMIT 1;";
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
		$_SESSION['Hometown'] = $_POST["Hometown"];
	
		Print_Text("pub_einstellungen_UserDateSaved");
        } 
	else
	{
		Print_Text(29);
		echo mysql_error( $con);
	}
	break;

}
}
include ("./inc/footer.php");
?>
