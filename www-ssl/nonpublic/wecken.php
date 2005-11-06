<?php
$title = "Himmel";
$header = "Weckdienst";

include ("./inc/header.php");
include ("./inc/funktion_user.php");

if( isset($_POST["eintragen"]))
	if( $_POST["eintragen"] == Get_Text("pub_wake_bouton") ) 
	{
		$SQL = "INSERT INTO Wecken (`UID`, `Date`, `Ort`, `Bemerkung`) ".
			"VALUES (".$_SESSION['UID'].", \"". $_POST["Date"]. "\", \"". $_POST["Ort"]. 
				"\", \"". $_POST["Bemerkung"]. "\") ";
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) 
			Print_Text(4);
	}
if( isset($_GET["eintragen"]))
	if ($_GET["eintragen"] == "loeschen") 
	{
		$SQL = "Delete from Wecken where UID = ".$_SESSION['UID']." and ID = ". $_GET["weckID"]." limit 1";
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1)
			Print_Text(4); 
	}
?>

<? echo Get_Text("Hello").$_SESSION['Nick'].",<br>".Get_Text("pub_wake_beschreibung")?>
<br><br>
<? echo Get_Text("pub_wake_beschreibung2"); ?>
<br><br>
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
        <tr class="contenttopic">
                <th align="left"><? echo Get_Text("pub_wake_Datum"); ?></th>
                <th align="left"><? echo Get_Text("pub_wake_Ort"); ?></th>
		<th align="left"><? echo Get_Text("pub_wake_Bemerkung"); ?></th>
		<th align="left"><? echo Get_Text("pub_wake_change"); ?></th>
        </tr>
						
<?
  $sql = "Select * from Wecken where UID='".$_SESSION['UID']."' order by Date asc";
  $Erg = mysql_query($sql, $con);
  $count = mysql_num_rows($Erg);

  for ($i=0; $i < $count; $i++) {
  $row=mysql_fetch_row($Erg);
?>
	<tr class="content">
		<td align="left"><? echo mysql_result($Erg, $i, "Date"); ?> </td>
		<td align="left"><? echo mysql_result($Erg, $i, "Ort"); ?> </td>
		<td align="left"><? echo mysql_result($Erg, $i, "Bemerkung"); ?> </td>
		<td align="left"><a href="./wecken.php?eintragen=loeschen&weckID=<? 
			echo mysql_result($Erg, $i, "ID")."\">".Get_Text("pub_wake_del"); ?></a></td>
	</tr>
<?

  }
?>
</table>
<br><br>
<? echo Get_Text("pub_wake_Text2"); ?><br><br>

<form action="wecken.php" method="post">
<table>
 <tr>
 	<td align="right"><? echo Get_Text("pub_wake_Datum"); ?>:</td>
 	<td align="left"><input type="text" name="Date" value="2003-08-05 08:00:00"></td>
 </tr>
 <tr>
 	<td align="right"><? echo Get_Text("pub_wake_Ort"); ?></td>
	<td align="left"><input type="text" name="Ort" value="Tent 23"></td>
 </tr>
 <tr>
 	<td align="right"><? echo Get_Text("pub_wake_Bemerkung"); ?></td>
	<td align="left"><textarea name="Bemerkung" rows="5" cols="40">knock knock leo, follow the white rabbit to the blue tent</textarea></td>
 </tr>
</table>
<input type="submit" name="eintragen" value="<? echo Get_Text("pub_wake_bouton"); ?>">
</form>
<?
include ("./inc/footer.php");
?>
