<?PHP

$title = "MakeNewUser";
$header = "Make New User";
$Page["Public"]="N^";
include ("./inc/header.php");
include ("./inc/db.php");
include ("./inc/crypt.php");

//User Anzahl
$SQL3 = "SELECT * FROM `User`";
$Erg3 = mysql_query($SQL3, $con);
echo "\n<!-- ". mysql_num_rows($Erg3). " //-->\n\n";

if( !isset($_POST["action"]) )
	$_POST["action"]="new";

if( $_POST["action"]=="newsave")
{
	$eNick = trim($_POST["eNick"]);
	if( strlen($_POST["eNick"]) < 2 )
	{
		$error= "<h3>error: nick '".$_POST["eNick"]."' is to short (min. 2 characters)</h3>";
	}
	elseif( strlen($_POST["eemail"]) <= 7 ||
		strstr($_POST["eemail"], "@") == FALSE ||
		strstr($_POST["eemail"], ".") == FALSE )
	{
		$error= "<h3>error: e-mail address is not correct</h3>";
	}
	elseif( $_POST["ePasswort"] != $_POST["ePasswort2"] )
	{
		$error= "error: passswords are not identical";
	}
	elseif( strlen($_POST["ePasswort"]) < 6 )
	{
		$error= "error: password is to short (min. 6 characters)";
	}
	else
	{
		$_POST["ePasswort"] =  PassCrypt($_POST["ePasswort"]);
		$SQL = "INSERT INTO `User` (`Nick`, `Name`, `Vorname`, `Alter`, `Telefon`, `DECT`, `Handy`, ".
		       "`email`, `Size`, `Passwort`) ".
		       "VALUES ('". $_POST["eNick"]. "', '". $_POST["eName"]. "', '". $_POST["eVorname"]. 
		       "', '". $_POST["eAlter"]. "', '". $_POST["eTelefon"]. "', '". $_POST["eDECT"]. 
		       "', '". $_POST["eHandy"]. "', '". $_POST["eemail"]. "', '". $_POST["eSize"]. 
		       "', '". $_POST["ePasswort"]. "');";
		$Erg = mysql_query($SQL, $con);
		if ($Erg != 1) 
			echo "error: can't save personal informations...<br><h6>(error: ".mysql_error($con).")</h6>";
		else
		{
			echo "personal informations was saved...<br>";
	
			$SQL2 = "SELECT UID FROM `User` WHERE Nick='". $_POST["eNick"]. "';";
			$Erg2 = mysql_query($SQL2, $con);
			$Data = mysql_fetch_array($Erg2);
	
			$SQL3 = "INSERT INTO `UserCVS` (`UID`) VALUES (". $Data["UID"]. ");";
			$Erg3 = mysql_query($SQL3, $con);
			if ($Erg3 != 1)
			{
			        echo "error: can't save userright... <br><h6>(".mysql_error($con).")</h6>";
			}
			else
			{
				echo "userright was saved...<br>";
				echo "<br><br> Your acount was sucsessfull creat, hafe al lot of fun.";
			}
		}
	}
}

if( $_POST["action"] == "new" || isset($error))
{
	  if( !isset($error) )
	  	echo "If you wont to be an angel please insert your personal information into this form: <br>";
	  else
	  	echo $error;
	  echo "\n\n<form action=\"https://". $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']. "\" method=\"POST\">\n";
	  echo "<table>\n";
	  echo "  <tr><td>nick*</td><td><input type=\"text\" size=\"40\" name=\"eNick\" value=\"". 
	  	$_POST["eNick"]. "\"></td></tr>\n";
	  echo "  <tr><td>last name</td><td><input type=\"text\" size=\"40\" name=\"eName\" value=\"". 
	  	$_POST["eName"]. "\"></td></tr>\n";
	  echo "  <tr><td>first name</td><td><input type=\"text\" size=\"40\" name=\"eVorname\" value=\"". 
	  	$_POST["eVorname"]. "\"></td></tr>\n";
	  echo "  <tr><td>age</td><td><input type=\"text\" size=\"40\" name=\"eAlter\" value=\"".
	  	$_POST["eAlter"]. "\"></td></tr>\n";
	  echo "  <tr><td>phone</td><td><input type=\"text\" size=\"40\" name=\"eTelefon\" value=\"".
	  	$_POST["eTelefon"]. "\"></td></tr>\n";
	  echo "  <tr><td>DECT on congress</td><td><input type=\"text\" size=\"40\" name=\"eDECT\" value=\"". 
	  	$_POST["eDECT"]. "\"></td></tr>\n";
	  echo "  <tr><td>mobile</td><td><input type=\"text\" size=\"40\" name=\"eHandy\" value=\"".
	  	$_POST["eHandy"]. "\"></td></tr>\n";
	  echo "  <tr><td>e-mail*</td><td><input type=\"text\" size=\"40\" name=\"eemail\" value=\"".
	  	$_POST["eemail"]. "\"></td></tr>\n";
	  echo "  <tr><td>T-Shirt size*</td><td><input type=\"text\" size=\"40\" name=\"eSize\" value=\"". 
	  	$_POST["eSize"]. "\"></td></tr>\n";
	  echo "  <tr><td>password*</td><td><input type=\"password\" size=\"40\" name=\"ePasswort\"></td></tr>\n";
	  echo "  <tr><td>password Confirm*</td><td><input type=\"password\" size=\"40\" name=\"ePasswort2\"></td></tr>\n";
	  echo "</table>\n";
	  echo "<input type=\"hidden\" name=\"action\" value=\"newsave\">\n";
	  echo "<input type=\"submit\" value=\"register me as an engel\">\n";
	  echo "</form>";
	  echo "\n\n\n\t* entry required!\n";
}

include ("./inc/footer.php");
?>

