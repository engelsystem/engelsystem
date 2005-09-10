<h4>&nbsp;Tage </h4>

<?

include ("./inc/db.php");

$SQL = "SELECT Date FROM `Schichtplan` ORDER BY 'Date'";
$Erg = mysql_query($SQL, $con);
if (!isset($ausdatum)) $ausdatum = substr(mysql_result($Erg, $i , 0), 0,10);
for ($i = 0 ; $i < mysql_fetch_row($Erg) ; $i++)
  if ($tmp != substr(mysql_result($Erg, $i , 0), 0,10)) {
      $tmp =  substr(mysql_result($Erg, $i , 0), 0,10);
//     echo "<li><a href='".basename(getenv("PATH_INFO"))."?ausdatum=$tmp'>$tmp</a></li>";
      echo "\t<li><a href='./schichtplanneu.php?ausdatum=$tmp'>$tmp</a></li>\n";
}
	      
?>
