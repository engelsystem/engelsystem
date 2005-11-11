<?

$Menu["Path"] = "nonpublic/";
$Menu["Name"] = Get_Text("pub_menu_menuname");
$Menu["Entry"][0]["File"] = "news.php";
$Menu["Entry"][0]["Name"] = Get_Text("pub_menu_news");
$Menu["Entry"][1]["File"] = "engelbesprechung.php";	
$Menu["Entry"][1]["Name"] = Get_Text("pub_menu_Engelbesprechung");
$Menu["Entry"][2]["File"] = "schichtplan.php";
$Menu["Entry"][2]["Name"] = Get_Text("pub_menu_Schichtplan");
$Menu["Entry"][3]["File"] = "myschichtplan.php";
$Menu["Entry"][3]["Name"] = Get_Text("pub_menu_mySchichtplan");
$Menu["Entry"][4]["File"] = "schichtplan_beamer.php";
$Menu["Entry"][4]["Name"] = Get_Text("pub_menu_SchichtplanBeamer");
$Menu["Entry"][5]["File"] = "wecken.php";
$Menu["Entry"][5]["Name"] = Get_Text("pub_menu_Wecken");
$Menu["Entry"][6]["File"] = "waeckliste.php";
$Menu["Entry"][6]["Name"] = Get_Text("pub_menu_Waeckerlist");
$Menu["Entry"][7]["File"] = "faq.php";
$Menu["Entry"][7]["Name"] = Get_Text("pub_menu_questionEngel");
$Menu["Entry"][8]["File"] = "einstellungen.php";
$Menu["Entry"][8]["Name"] = Get_Text("pub_menu_Einstellungen");
$Menu["Entry"][8]["Line"] = "<br>";
$Menu["Entry"][9]["File"] = "../logout.php";
$Menu["Entry"][9]["Name"] = Get_Text("pub_menu_Abmelden");


if ($_SESSION['CVS']["admin/index.php"] == "Y") 
{
	$MenuAdmin["Path"] = "";
	$MenuAdmin["Name"] = "Erzengel";
	$MenuAdmin["Entry"][0]["File"] = "../admin/index.php";
	$MenuAdmin["Entry"][0]["Name"] = "Erzengel-Men&uuml;";
	$MenuAdmin["Entry"][1]["File"] = "../index.php";
	$MenuAdmin["Entry"][1]["Name"] = "Login-Men&uuml;";
} // MenueShowAdminSection

?>
