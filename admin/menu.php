<?PHP
include ("./inc/funktion_faq.php");

$Menu["Path"] = "admin/";
$Menu["Name"] = "Kategorie";
$Menu["Entry"][1]["File"] = "room.php";
$Menu["Entry"][1]["Name"] = "R&auml;ume";
$Menu["Entry"][12]["File"] = "EngelType.php";
$Menu["Entry"][12]["Name"] = "Engeltypen";
$Menu["Entry"][2]["File"] = "schichtplan.php";
$Menu["Entry"][2]["Name"] = "Schichtplan";
$Menu["Entry"][2]["Line"] = "<br>";
$Menu["Entry"][3]["File"] = "dbUpdateFromXLS.php";
$Menu["Entry"][3]["Name"] = "UpdateDB";
$Menu["Entry"][13]["File"] = "dect.php";
$Menu["Entry"][13]["Name"] = "Dect";
$Menu["Entry"][13]["Line"] = "<br>";
$Menu["Entry"][4]["File"] = "user.php";
$Menu["Entry"][4]["Name"] = "Engelliste";
$Menu["Entry"][5]["File"] = "aktiv.php";
$Menu["Entry"][5]["Name"] = "Aktivliste";
$Menu["Entry"][6]["File"] = "tshirt.php";
$Menu["Entry"][6]["Name"] = "T-Shirtausgabe";
$Menu["Entry"][6]["Line"] = "<br><br>";
$Menu["Entry"][7]["File"] = "news.php";
$Menu["Entry"][7]["Name"] = "News-Verwaltung";
$Menu["Entry"][8]["File"] = "faq.php";
$Menu["Entry"][8]["Name"] = "FAQ (". noAnswer(). ")";
$Menu["Entry"][9]["File"] = "free.php";
$Menu["Entry"][9]["Name"] = "Freie Engel";
$Menu["Entry"][9]["Line"] = "<br><br>";
$Menu["Entry"][11]["File"] = "sprache.php";
$Menu["Entry"][11]["Name"] = "Language";
$Menu["Entry"][11]["Line"] = "<br><br>";
$Menu["Entry"][10]["File"] = "list.php";
$Menu["Entry"][10]["Name"] = "Debug";

if ($_SESSION['CVS']["MenueShowAdminSection"] == "Y") {
        $MenuAdmin["Name"] = "Erzengel";
        $MenuAdmin["Entry"][0]["File"] = "../nonpublic/index.php";
        $MenuAdmin["Entry"][0]["Name"] = "Engel-Men&uuml;";
} // MenueShowAdminSection
			


?>
