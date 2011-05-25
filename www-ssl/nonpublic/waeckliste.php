<?php
$title = "Himmel";
$header = "Weckdienst - Liste der zu weckenden Engel";

include ("../../../camp2011/includes/header.php");

?>

<?PHP echo Get_Text("Hello"). $_SESSION['Nick'].",<br>\n".
     	   Get_Text("pub_waeckliste_Text1")?>
<br><br>
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
        <tr class="contenttopic">
		<th align="left"><?PHP echo Get_Text("pub_waeckliste_Nick");?></th>
		<th align="left"><?PHP echo Get_Text("pub_waeckliste_Datum");?></th>
                <th align="left"><?PHP echo Get_Text("pub_waeckliste_Ort");?></th>
		<th align="left"><?PHP echo Get_Text("pub_waeckliste_Comment");?></th>
        </tr>
						
<?PHP
  $sql = "SELECT * FROM `Wecken` ORDER BY `Date` ASC";
  $Erg = mysql_query($sql, $con);
  $count = mysql_num_rows($Erg);

  for ($i=0; $i < $count; $i++) {
  $row=mysql_fetch_row($Erg);
?>
	<tr class="content">
		<td align="left"><?PHP echo UID2Nick(mysql_result($Erg, $i, "UID")); ?> </td>
		<td align="left"><?PHP echo mysql_result($Erg, $i, "Date"); ?> </td>
		<td align="left"><?PHP echo mysql_result($Erg, $i, "Ort"); ?> </td>
		<td align="left"><?PHP echo mysql_result($Erg, $i, "Bemerkung"); ?> </td>
	</tr>
<?PHP
  }
?>
</table>
<?PHP
include ("../../../camp2011/includes/footer.php");
?>
