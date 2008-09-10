<?PHP

function funktion_db_list($Table_Name) {

	global $con;

$SQL = "SELECT * FROM `".$Table_Name."`";
$Erg = mysql_query($SQL, $con);

// anzahl zeilen
$Zeilen  = mysql_num_rows($Erg);

$Anzahl_Felder = mysql_num_fields($Erg);

echo "<table border=1>";

echo "<tr>";
for ($m = 0 ; $m < $Anzahl_Felder ; $m++) {
  echo "<th>". mysql_field_name($Erg, $m). "</th>";
  }
echo "</tr>";

for ($n = 0 ; $n < $Zeilen ; $n++) {
  echo "<tr>";
  for ($m = 0 ; $m < $Anzahl_Felder ; $m++) {
    echo "<td>".mysql_result($Erg, $n, $m). "</td>"; 
  }
  echo "</tr>";
}

echo "</table>";
}

?>
