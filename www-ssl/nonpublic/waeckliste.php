<?php
include ("./inc/funktion_user.php");

$title = "Himmel";
$header = "Weckdienst - Liste der zu weckenden Engel";

include ("./inc/header.php");

?>

<? echo Get_Text(1). $_SESSION['Nick'].",<br>\n".
        Get_Text("pub_waeckliste_Text1")?>
<br><br>
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
        <tr class="contenttopic">
		<th align="left"><? echo Get_Text("pub_waeckliste_Nick");?></th>
		<th align="left"><? echo Get_Text("pub_waeckliste_Datum");?></th>
                <th align="left"><? echo Get_Text("pub_waeckliste_Ort");?></th>
		<th align="left"><? echo Get_Text("pub_waeckliste_Comment");?></th>
        </tr>
						
<?
  $sql = "Select * from Wecken order by Date asc";
  $Erg = mysql_query($sql, $con);
  $count = mysql_num_rows($Erg);

  for ($i=0; $i < $count; $i++) {
  $row=mysql_fetch_row($Erg);
?>
	<tr class="content">
		<td align="left"><? echo UID2Nick(mysql_result($Erg, $i, "UID")); ?> </td>
		<td align="left"><? echo mysql_result($Erg, $i, "Date"); ?> </td>
		<td align="left"><? echo mysql_result($Erg, $i, "Ort"); ?> </td>
		<td align="left"><? echo mysql_result($Erg, $i, "Bemerkung"); ?> </td>
	</tr>
<?

  }
?>
</table>
<?
include ("./inc/footer.php");
?>
