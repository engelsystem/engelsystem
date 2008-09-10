<?PHP
$title = "Anmeldung zum Chaos-Engel";
$header = "";
$success = "none"; 
include ("../includes/config.php");
include ("../includes/header.php");
include ("../includes/config_db.php");
include ("../includes/crypt.php");

		
if( isset($_POST["send"]))
{
	$eNick = trim($_POST["Nick"]);
	if( $_POST["Alter"]=="")	$_POST["Alter"] = 0;
	
	//user vorhanden?
	$SQLans = "SELECT UID FROM `User` WHERE `Nick`='". $_POST["Nick"]. "'";
	$Ergans = mysql_query($SQLans, $con);
	
	if( strlen($_POST["Nick"]) < 2 ) 
	{
		$error= Get_Text("makeuser_error_nick1"). $_POST["Nick"]. Get_Text("makeuser_error_nick2");
	} 
	elseif( mysql_num_rows( $Ergans) > 0)
	{
		$error= Get_Text("makeuser_error_nick1"). $_POST["Nick"]. Get_Text("makeuser_error_nick3");
	}
	elseif( strlen($_POST["email"]) <= 6 && strstr($_POST["email"], "@") == FALSE && 
		strstr($_POST["email"], ".") == FALSE ) 
	{
		$error= Get_Text("makeuser_error_mail");
	} 
	elseif( !is_numeric($_POST["Alter"])) 
	{
		$error= Get_Text("makeuser_error_Alter");
	}
	elseif( $_POST["Passwort"] != $_POST["Passwort2"] ) 
	{
		$error= Get_Text("makeuser_error_password1");
	} 
	elseif( strlen($_POST["Passwort"]) < 6 ) 
	{
		$error= Get_Text("makeuser_error_password2");
	} 
	else 
	{
		$_POST["Passwort"] = PassCrypt($_POST["Passwort"]);
		unset($_POST["Passwort2"]);

		$SQL = "INSERT INTO `User` (".
				"`Nick` , ".	"`Name` , ".
				"`Vorname`, ".	"`Alter` , ".
				"`Telefon`, ".	"`DECT`, ".
				"`Handy`, ".	"`email`, ".
				"`ICQ`, ".	"`jabber`, ".
				"`Size`, ".	"`Passwort`, ".
				"`Art` , ".	"`kommentar`, ".
				"`Hometown`,".  "`CreateDate` ) ".
			"VALUES ( ".
				"'". $_POST["Nick"]. "', ".		"'". $_POST["Name"]. "', ".
				"'". $_POST["Vorname"]. "', ".		"'". $_POST["Alter"]. "', ".
				"'". $_POST["Telefon"]. "', ".		"'". $_POST["DECT"]. "', ".
				"'". $_POST["Handy"]. "', ".		"'". $_POST["email"]. "', ".
				"'". $_POST["ICQ"]. "', ".		"'". $_POST["jabber"]. "', ".
				"'". $_POST["Size"]. "', ".		"'". $_POST["Passwort"]. "', ".
				"'". $_POST["Art"]. "', ".		"'". $_POST["kommentar"]. "', ".
				"'". $_POST["Hometown"]. "',".		"NOW());";
		$Erg = mysql_query($SQL, $con);

		if ($Erg != 1)
		{
			echo Get_Text("makeuser_error_write1"). "<br>\n";
			$error = mysql_error($con);
		}
		else
		{
			echo "<p class=\"important\">". Get_Text("makeuser_writeOK"). "\n";

			$SQL2 = "SELECT `UID` FROM `User` WHERE `Nick`='". $_POST["Nick"]. "';";
			$Erg2 = mysql_query($SQL2, $con);
			$Data = mysql_fetch_array($Erg2);

			$SQL3 = "INSERT INTO `UserCVS` (`UID`) VALUES ('". $Data["UID"]. "');";
			$Erg3 = mysql_query($SQL3, $con);
			if ($Erg3 != 1)
			{
				echo "<h1>". Get_Text("makeuser_error_write2"). "<br>\n";
				$error = mysql_error($con);
			}
			else
			{
				echo Get_Text("makeuser_writeOK2"). "<br>\n";
				echo "<h1>". Get_Text("makeuser_writeOK3"). "</h1>\n";
			}
			echo Get_Text("makeuser_writeOK4"). "</p><p></p>\n<br><br>\n";
			$success="any";

		}
	}
	if( !isset($error) ){
		echo Get_Text("makeuser_text4"). "<a href=";
		echo($url). ">Login</a><br>\n";
	}else{ 
                echo "<p class=\"warning\">\n$error\n</p>\n\n";
	}
}
else
{
	//init vars
	$_POST["Nick"] = "";
	$_POST["Name"] = "";
	$_POST["Vorname"] = "";
	$_POST["Alter"] = "";
	$_POST["Telefon"] = "";
	$_POST["DECT"] = "";
	$_POST["Handy"] = "";
	$_POST["email"] = "";
	$_POST["ICQ"] = "";
	$_POST["jabber"] = "";
	$_POST["Size"] = "";
	$_POST["Art"] = "";
	$_POST["kommentar"] = "";
	$_POST["Hometown"] = "";
}
if( $success=="none" ){
echo "<h1>".Get_Text("makeuser_text0")."</h1>". "<h2>". Get_Text("makeuser_text1"). "</h2>";
echo "\t<form action=\"\" method=\"post\">\n";
echo "\t\t<table>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Nickname").
	"*</td><td><input type=\"text\" size=\"40\" name=\"Nick\" value=\"". $_POST["Nick"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Nachname"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Name\" value=\"". $_POST["Name"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Vorname"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Vorname\" value=\"". $_POST["Vorname"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Alter"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Alter\" value=\"". $_POST["Alter"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Telefon"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Telefon\" value=\"". $_POST["Telefon"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_DECT"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"DECT\" value=\"". $_POST["DECT"]. "\"></td><td>\n";
echo "\t\t\t<!--a href=\"https://21c3.ccc.de/wiki/index.php/POC\"><img src=\"./pic/external.png\" alt=\"external: \">DECT</a--></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Handy"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Handy\" value=\"". $_POST["Handy"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_E-Mail"). 
	"*</td><td><input type=\"text\" size=\"40\" name=\"email\" value=\"". $_POST["email"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>ICQ</td><td><input type=\"text\" size=\"40\" name=\"ICQ\" value=\"". $_POST["ICQ"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>jabber</td><td><input type=\"text\" size=\"40\" name=\"jabber\" value=\"". $_POST["jabber"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_T-Shirt"). 
	" Gr&ouml;sse*</td><td align=\"left\">\n";
echo "\t\t\t<select name=\"Size\">\n";
echo "\t\t\t\t<option value=\"S\"";	if ($_POST["Size"]=="S") 	echo " selected";	echo ">S</option>\n";
echo "\t\t\t\t<option value=\"M\"";	if ($_POST["Size"]=="M") 	echo " selected";	echo ">M</option>\n";
echo "\t\t\t\t<option value=\"L\"";	if ($_POST["Size"]=="L") 	echo " selected";	echo ">L</option>\n";
echo "\t\t\t\t<option value=\"XL\"";	if ($_POST["Size"]=="XL") 	echo " selected";	echo ">XL</option>\n";
echo "\t\t\t\t<option value=\"XXL\"";	if ($_POST["Size"]=="XXL") 	echo " selected";	echo  ">XXL</option>\n";
echo "\t\t\t\t<option value=\"XXXL\"";	if ($_POST["Size"]=="XXXL") 	echo " selected";	echo  ">XXXL</option>\n";
echo "\t\t\t</select>\n";
echo "\t\t\t</td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Engelart"). 
	"</td><td align=\"left\">\n";
echo "\t\t\t<select name=\"Art\">\n";
echo "\t\t\t\t<option value=\"Konferenz\""; 	if ($_POST["Art"]=="Konferenz")		echo " selected"; 
	echo ">Konferenz</option>\n";
//echo "\t\t\t\t<option value=\"Dokumentation\"";	if ($_POST["Art"]=="Dokumentation")	echo " selected";
//	echo ">Dokumentation</option>\n";
echo "\t\t\t\t<option value=\"Eingang\"";	if ($_POST["Art"]=="Eingang")		echo " selected";
	echo ">Eingang</option>\n";
echo "\t\t\t\t<option value=\"Schutzengel\"";	if ($_POST["Art"]=="Schutzengel")	echo " selected";
	echo ">Schutzengel</option>\n";
echo "\t\t\t\t<option value=\"Netzengel\"";	if ($_POST["Art"]=="Netzengel")		echo " selected";
	echo ">Netzengel</option>\n";
echo "\t\t\t\t<option value=\"Rettung\"";	if ($_POST["Art"]=="Rettung")		echo " selected";
	echo ">Rettung</option>\n";
echo "\t\t\t\t<option value=\"Verpflegung\"";	if ($_POST["Art"]=="Verpflegung")	echo " selected";
	echo ">Verpflegung</option>\n";
echo "\t\t\t\t<option value=\"egal\"";		if ($_POST["Art"]=="egal")		echo " selected";
	echo ">Egal-Engel</option>\n";
echo "\t\t\t</select>\n";
echo "\t\t\t</td><td><!--a href=\"https://www.ccc.de/congress/2004/engel.de.html#d5e23\"><img src=\"./pic/external.png\" alt=\"external: \" /> Congress 21C3</a--></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td>". Get_Text("makeuser_text2"). "</td>\n";
echo "\t\t\t<td><textarea rows=\"5\" cols=\"40\" name=\"kommentar\">". $_POST["kommentar"]. "</textarea></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Hometown"). 
	"</td><td><input type=\"text\" size=\"40\" name=\"Hometown\" value=\"". $_POST["Hometown"]. "\"></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Passwort"). 
	"*</td><td><input type=\"password\" size=\"40\" name=\"Passwort\"/></td></tr>\n";
echo "\t\t\t<tr><td>". Get_Text("makeuser_Passwort2"). 
	"*</td><td><input type=\"password\" size=\"40\" name=\"Passwort2\"/></td></tr>\n";
echo "\t\t\t<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"send\" value=\"". 
	Get_Text("makeuser_Anmelden"). "\"/></td></tr>\n";
echo "\t\t</table>\n";
echo "\t</form>\n";
Print_Text("makeuser_text3");
}
include ("../includes/footer.php");
?>

