<?PHP

$title = "Engel Arrived";
$header = "Engel was arrived";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");


If (IsSet($_GET["arrived"])) {

	$SQL="UPDATE `User` SET `Gekommen`='1' WHERE `UID`='". $_GET["arrived"]. "' limit 1";
	$Erg = db_query($SQL, "Set User as Gekommen");
        if ($Erg == 1) {
		echo "<h2>". Get_Text("pri_userArrived_WriteOK"). " \"". UID2Nick($_GET["arrived"]). "\"</h2>";
        } else {
		echo "<h1>". Get_Text("pri_userArrived_WriteError"). " \"". UID2Nick($_GET["arrived"]). "\"</h1>";
        }
}

echo Get_Text("pri_userArrived_Text1"). "<br>";
echo Get_Text("pri_userArrived_Text2"). "<br><br>";

echo Get_Text("pri_userArrived_TableToppic");
$SQL = "SELECT * FROM `User` ORDER BY `Nick` ASC"; 
$Erg = mysql_query($SQL, $con);

$rowcount = mysql_num_rows($Erg);

echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
echo "\t<tr class=\"contenttopic\">\n";
echo "\t\t<td>". Get_Text("pri_userArrived_TableTD_Nick"). "</td>\n";
echo "\t\t<td>". Get_Text("pri_userArrived_TableTD_ArrivedShow"). "</td>\n";
echo "\t\t<td>". Get_Text("pri_userArrived_TableTD_ArrivedSet"). "</td>\n";
echo "\t</td>\n";

for ($i=0; $i<$rowcount; $i++){
	echo "\t<tr class=\"content\">\n";
	$eUID=mysql_result($Erg, $i, "UID");
	echo "\t\t<td>".UID2Nick($eUID)."</td>\n";
	echo "\t\t<td>".mysql_result($Erg, $i, "Gekommen")."</td>\n";

	if (mysql_result($Erg, $i, "Gekommen") =="1") 
	{
		echo "\t\t<td>". Get_Text("pri_userArrived_TableEntry_Arrived"). "</td>";
	} else {
		echo "\t\t<td><a href=\"./userArrived.php?arrived=$eUID\">". Get_Text("pri_userArrived_TableEntry_Set"). "</a></td>";
	}
	echo "\t</tr>\n";
}
echo "</table>";

include ("../../includes/footer.php");
?>

