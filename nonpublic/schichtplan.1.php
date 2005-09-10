<h4>&nbsp;Tage </h4>

<?

$SQL = "SELECT `DateS` FROM `Shifts` ORDER BY `DateS`";
$Erg = mysql_query($SQL, $con);

for ($i = 0 ; $i < mysql_fetch_row($Erg) ; $i++)
  if ($tmp != substr(mysql_result($Erg, $i , 0), 0,10)) {
      $tmp =  substr(mysql_result($Erg, $i , 0), 0,10);
      echo "\t<li><a href='./schichtplan.php?ausdatum=$tmp";
      // ist ein raum gesetzt?
      if (IsSet($raum)) {
      	echo "&raum=$raum";
      }
      echo "'>$tmp</a></li>\n";
}
	      
?>
