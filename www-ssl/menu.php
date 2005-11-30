<?PHP

$Menu["Path"] = "";
$Menu["Name"] = Get_Text("menu_Name");
$Menu["Entry"][0]["File"] = "index.php";
$Menu["Entry"][0]["Name"] = Get_Text("menu_index");
$Menu["Entry"][0]["Line"] = "<br>";
$Menu["Entry"][1]["File"] = "faq.php";
$Menu["Entry"][1]["Name"] = Get_Text("menu_FAQ");
$Menu["Entry"][1]["Line"] = "<br>";
$Menu["Entry"][2]["File"] = "lageplan.php";
$Menu["Entry"][2]["Name"] = Get_Text("menu_plan");
$Menu["Entry"][2]["Line"] = "<br>";
$Menu["Entry"][3]["File"] = "makeuser.php";
$Menu["Entry"][3]["Name"] = Get_Text("menu_MakeUser");
$Menu["Entry"][3]["Line"] = "<br>";
$Menu["Entry"][4]["File"] = "nonpublic/schichtplan_beamer.php";
$Menu["Entry"][4]["Name"] = Get_Text("pub_menu_SchichtplanBeamer");

if ($_SESSION['CVS']["nonpublic/news.php"] == "Y") 
{
	$MenuAdmin["Path"] = "";
	$MenuAdmin["Name"] = Get_Text("pub_menu_menuname");
	$MenuAdmin["Entry"][0]["File"] = "admin/index.php";
	$MenuAdmin["Entry"][0]["Name"] = "Erzengel-Men&uuml;";
	$MenuAdmin["Entry"][1]["File"] = "nonpublic/news.php";
	$MenuAdmin["Entry"][1]["Name"] = "Engel-Men&uuml;";
} // MenueShowAdminSection

?>
