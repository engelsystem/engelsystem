<?php
$title = "Himmel";
$header = "Schichtpl&auml;ne";
$submenus = 1;
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktionen.php");

?>

Hallo <? echo $_SESSION['Nick']?>,<br>
hier kannst du dich f&uuml;r Schichten in der Aula eintragen. Dazu w&auml;hle einfach eine freie Schicht und klicke auf den Link.<br><br>


<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">

<?php 

if (!IsSet($ausdatum)) {

  $sql = "Select Date from Schichtplan order by Date ASC limit 0, 1";
  $Erg = mysql_query($sql, $con);
  $ausdatum = substr(mysql_result($Erg,0,"Date"),0,10);
  }
      
//Zeit Ausgeben
#for ($i = 0; $i < 24; ++$i){
#  $Spalten[$i] = "\t<tr class=\"content\">\n";
#  $Spalten[$i].= "\t\t<td>";
#  if($i < 10){
#     $Spalten[$i].= "0";
#     $Spalten[$i].= "$i:00";
#     $Spalten[$i].= "</td>\n";
#	    }
#	} 
#    $rowcount = mysql_num_rows($res);
		    


for ($zeit = 0; $zeit < 24; $zeit++) 
    {
    $zzeit = $zeit;
    if ($zzeit < 10)
       {
         $zzeit = "0".$zzeit;
	}

$SSQL = "SELECT * FROM Schichtplan WHERE Date = \"2002-12-27 $zzeit:00:00\" AND RID = 1";
$SERG = mysql_query($SSQL, $con);
$SRES = mysql_fetch_row($SERG);

$USQL = "SELECT UID FROM Schichtbelegung WHERE SID = \"".$SRES[0]."\"";
$UERG = mysql_query($USQL, $con);
$URES = mysql_fetch_row($UERG);

$NSQL = "SELECT Nick FROM User WHERE UID = \"$URES[0]\"";
$NERG = mysql_query($NSQL, $con);
$NRES = mysql_fetch_row($NERG);

echo "\t<tr class=\"contenttopic\">\n";
echo "\t\t<td>\n";
echo $zzeit.":00";
echo "\t\t</td>";
echo "\t\t<td>\n";
echo $NRES[0];
echo "\t\t</td>";

$SSQL2 = "SELECT * FROM Schichtplan WHERE Date = \"2002-12-28 $zzeit:00:00\" AND RID = 1";
$SERG2 = mysql_query($SSQL2, $con);
$SRES2 = mysql_fetch_row($SERG2);

$USQL2 = "SELECT UID FROM Schichtbelegung WHERE SID = \"".$SRES2[0]."\"";
$UERG2 = mysql_query($USQL2, $con);
$URES2 = mysql_fetch_row($UERG2);

$NSQL2 = "SELECT Nick FROM User WHERE UID = \"$URES2[0]\"";
$NERG2 = mysql_query($NSQL2, $con);
$NRES2 = mysql_fetch_row($NERG2);

echo "\t\t<td>\n";
echo $NRES2[0];
echo "\t\t</td>";

$SSQL3 = "SELECT * FROM Schichtplan WHERE Date = \"2002-12-29 $zzeit:00:00\" AND RID = 1";
$SERG3 = mysql_query($SSQL3, $con);
$SRES3 = mysql_fetch_row($SERG3);

$USQL3 = "SELECT UID FROM Schichtbelegung WHERE SID = \"".$SRES3[0]."\"";
$UERG3 = mysql_query($USQL3, $con);
$URES3 = mysql_fetch_row($UERG3);

$NSQL3 = "SELECT Nick FROM User WHERE UID = \"$URES3[0]\"";
$NERG3 = mysql_query($NSQL3, $con);
$NRES3 = mysql_fetch_row($NERG3);

echo "\t\t<td>\n";
echo $NRES3[0];
echo "\t\t</td>";
echo "\t</tr>";



}
echo "</table>\n";
include ("./inc/footer.php");
?>
