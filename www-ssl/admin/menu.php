<?PHP
include ("./inc/funktion_faq.php");

$Menu["Path"] = "admin/";
$Menu["Name"] = "Kategorie";
$Menu["Entry"][1]["File"] = "room.php";
$Menu["Entry"][1]["Name"] = Get_Text("pub_menu_Rooms");
$Menu["Entry"][12]["File"] = "EngelType.php";
$Menu["Entry"][12]["Name"] = Get_Text("pub_menu_Engeltypen");
$Menu["Entry"][2]["File"] = "schichtplan.php";
$Menu["Entry"][2]["Name"] = Get_Text("pub_menu_SchichtplanEdit");
$Menu["Entry"][2]["Line"] = "<br>";
$Menu["Entry"][3]["File"] = "dbUpdateFromXLS.php";
$Menu["Entry"][3]["Name"] = Get_Text("pub_menu_UpdateDB");
$Menu["Entry"][13]["File"] = "dect.php";
$Menu["Entry"][13]["Name"] = Get_Text("pub_menu_Dect");
$Menu["Entry"][13]["Line"] = "<br>";
$Menu["Entry"][4]["File"] = "user.php";
$Menu["Entry"][4]["Name"] = Get_Text("pub_menu_Engelliste");
$Menu["Entry"][14]["File"] = "userDefaultSetting.php";
$Menu["Entry"][14]["Name"] = Get_Text("pub_menu_EngelDefaultSetting");
$Menu["Entry"][5]["File"] = "aktiv.php";
$Menu["Entry"][5]["Name"] = Get_Text("pub_menu_Aktivliste");
$Menu["Entry"][6]["File"] = "tshirt.php";
$Menu["Entry"][6]["Name"] = Get_Text("pub_menu_T-Shirtausgabe");
$Menu["Entry"][6]["Line"] = "<br><br>";
$Menu["Entry"][7]["File"] = "news.php";
$Menu["Entry"][7]["Name"] = Get_Text("pub_menu_News-Verwaltung");
$Menu["Entry"][8]["File"] = "faq.php";
$Menu["Entry"][8]["Name"] = Get_Text("pub_menu_FAQ"). " (". noAnswer(). ")";
$Menu["Entry"][9]["File"] = "free.php";
$Menu["Entry"][9]["Name"] = Get_Text("pub_menu_FreeEngel");
$Menu["Entry"][9]["Line"] = "<br><br>";
$Menu["Entry"][11]["File"] = "sprache.php";
$Menu["Entry"][11]["Name"] = Get_Text("pub_menu_Language");
$Menu["Entry"][11]["Line"] = "<br><br>";
$Menu["Entry"][10]["File"] = "debug.php";
$Menu["Entry"][10]["Name"] = Get_Text("pub_menu_Debug");
$Menu["Entry"][15]["File"] = "Recentchanges.php";
$Menu["Entry"][15]["Name"] = Get_Text("pub_menu_Recentchanges");

if ($_SESSION['CVS']["nonpublic/index.php"] == "Y")
{
        $MenuAdmin["Name"] = "Erzengel";
        $MenuAdmin["Entry"][0]["File"] = "../nonpublic/news.php";
        $MenuAdmin["Entry"][0]["Name"] = "Engel-Men&uuml;";
	$MenuAdmin["Entry"][1]["File"] = "../index.php";
	$MenuAdmin["Entry"][1]["Name"] = "Login-Men&uuml;";
} // MenueShowAdminSection
			
?>
