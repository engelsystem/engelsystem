<?php
require_once ('../bootstrap.php');

$title = "Himmel";
$header = "Weckdienst - Liste der zu weckenden Engel";

include "includes/header.php";
?>

<p><?php echo Get_Text("Hello") . $_SESSION['Nick'] . ",<br />\n" . Get_Text("pub_waeckliste_Text1"); ?></p>
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
  <tr class="contenttopic">
    <th align="left"><?php echo Get_Text("pub_waeckliste_Nick");?></th>
    <th align="left"><?php echo Get_Text("pub_waeckliste_Datum");?></th>
    <th align="left"><?php echo Get_Text("pub_waeckliste_Ort");?></th>
    <th align="left"><?php echo Get_Text("pub_waeckliste_Comment");?></th>
  </tr>

<?php


$sql = "SELECT * FROM `Wecken` ORDER BY `Date` ASC";
$Erg = mysql_query($sql, $con);
$count = mysql_num_rows($Erg);

for ($i = 0; $i < $count; $i++) {
	$row = mysql_fetch_row($Erg);
?>
  <tr class="content">
    <td align="left"><?php echo UID2Nick(mysql_result($Erg, $i, "UID")); ?> </td>
    <td align="left"><?php echo mysql_result($Erg, $i, "Date"); ?> </td>
    <td align="left"><?php echo mysql_result($Erg, $i, "Ort"); ?> </td>
    <td align="left"><?php echo mysql_result($Erg, $i, "Bemerkung"); ?> </td>
  </tr>
<?php


}
?>
</table>

<?php


include "includes/footer.php";
?>
