<?
$title = "Himmel";
$header = "Deine pers&ouml;nlichen Einstellungen";
include ("./inc/header.php");
include ("./inc/crypt.php");

if (!IsSet($action)) {

echo Get_Text(1).$_SESSION['Nick'].",<br>\n\n";

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
	</table>
	<input type="submit" value="<?PHP Print_Text(10); ?>">
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
	<input type="submit" value="<?PHP Print_Text(10); ?>">
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
	</table>
	<input type="submit" value="<?PHP Print_Text(10); ?>">
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
	<input type="submit" value="<?PHP Print_Text(10); ?>">
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
	    <option value="0" name="eAvatar" <?php if ($_SESSION['Avatar'] == $i) { echo " selected"; } ?>> <?PHP Print_Text(24); ?> </option>
	    <?php
	    for ($i=1; $i <= $ANZ_AVATAR; $i++ ){
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
	<input type="submit" value="<?PHP Print_Text(10); ?>">
</form>


<?

} else {

switch ($action) {

case 'set':
  if ($new1==$new2){
	Print_Text(25); 
	$sql = "select * from User where UID=".$_SESSION['UID'];
	$Erg = mysql_query($sql, $con);
	if (PassCrypt($old)==mysql_result($Erg, $i, "Passwort")) {
		Print_Text(26);
		Print_Text(27);
		$usql = "update User set Passwort='".PassCrypt($new1)."' where UID=".$_SESSION['UID']." limit 1";
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

	$chsql="Update User set color= \"$colourid\" where UID = \"".$_SESSION['UID']."\" limit 1";
	$Erg = mysql_query($chsql, $con);
	$_SESSION['color']=$colourid;
	if ($Erg==1) {
		Print_Text(32);
	} else {
		Print_Text(29);
	}
																		
	break;

case 'sprache':

	$chsql="Update User set Sprache = \"$language\" where UID = \"".$_SESSION['UID']."\" limit 1";
	$Erg = mysql_query($chsql, $con);
	$_SESSION['Sprache']=$language;
	if ($Erg==1) {
		Print_Text(33);
	} else {
		Print_Text(29);
	}
																		
	break;


case 'avatar':
	$chsql="Update User set Avatar = \"$eAvatar\" where UID = \"".$_SESSION['UID']."\" limit 1";
        $Erg = mysql_query($chsql, $con);
	$_SESSION['Avatar']=$eAvatar;
	if ($Erg==1) {
		Print_Text(34);
        } else {
		Print_Text(29);
	}
        break;

case 'setUserData':
	$chsql= "UPDATE User SET ".
		"`Nick`='$eNick', `Name`='$eName', `Vorname`='$eVorname', ".
		"`Alter`='$eAlter', `Telefon`='$eTelefon', `Handy`='$eHandy', ".
		"`DECT`='$eDECT', `email`='$eemail' ".
		"WHERE UID='". $_SESSION['UID']. "' LIMIT 1;";
        $Erg = mysql_query($chsql, $con);

	if ($Erg==1) 
	{
		$_SESSION['Nick'] = $eNick;
		$_SESSION['Name'] = $eName;
		$_SESSION['Vorname'] = $eVorname;
		$_SESSION['Alter'] = $eAlter;
		$_SESSION['Telefon'] = $eTelefon;
		$_SESSION['Handy'] = $eHandy;
		$_SESSION['DECT'] = $eDECT;
		$_SESSION['email'] = $eemail;
	
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
