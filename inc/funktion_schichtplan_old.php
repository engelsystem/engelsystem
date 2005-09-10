<?php

function Ausgabe_Feld_Inhalt($RID, $SID) {
// gibt, nach übergabe der der SchichtID (SID) und der RaumID (RID),
// die eingetragenden und und offenden Schichteintäge zurück
  global $con;
  include ("./inc/config.php");
  //echo "####################", $Erg, "####################<br>";
  
  $SQL2 = "SELECT * FROM `Raeume` WHERE ";
  $SQL2.= "(RID = '".$RID."')";
  $Erg2 = mysql_query($SQL2, $con);
  

if( $_SESSION['UID'] == 1 )
{}
  $Temp = mysql_result( mysql_query("SELECT Name FROM `Schichtplan` WHERE (SID='$SID')", $con ), 0, "Name");
  if( ($Temp-1)!=-1 ) 
  	$Spalten.="<a href=\"". $CCC_Start. $Temp. $CCC_End. "\" target=\"_black\">$Temp:</a><br>";
  else
  	$Spalten.="<u>".$Temp.":</u><br>";
 
  for ($Uj = 2; $Uj < mysql_num_fields($Erg2); $Uj++) {
    if (mysql_result($Erg2, 0, $Uj)) {
    $Spalten.= "\n\t\t\t ".mysql_field_name($Erg2,
		$Uj).Get_Text("inc_schicht_engel"). " ";//.":<br>&nbsp;&nbsp;";
          
    $SQL3 = "SELECT * FROM `Schichtbelegung` WHERE ";
    $SQL3.= "((SID = '".$SID."') AND";
    $SQL3.= " (Art = '".$Uj."'))";
    $Erg3 = mysql_query($SQL3, $con);
    
    // Ausgabe bisherige Engel fuer die Schicht
    for ($Ui = 0; $Ui < mysql_num_rows($Erg3); $Ui++) 
    { 
      if ($Ui!=0) 
      {
      if ( substr($Spalten, strlen($Spalten)-4, 4 ) == "<br>" )
      	$Spalten = substr($Spalten, 0, strlen($Spalten)-4).",<br>&nbsp;&nbsp;";
      else 
    	$Spalten.= ", ";
      }
      elseif (mysql_num_rows($Erg3) == 1) 
      	$Spalten.= Get_Text("inc_schicht_ist"). ":<br>&nbsp;&nbsp;";
      else 
      	$Spalten.= Get_Text("inc_schicht_sind"). ":<br>&nbsp;&nbsp;";
      
      $Spalten.= UID2Nick(mysql_result($Erg3, $Ui, "UID"));
      // avatar anzeigen?
      $Spalten.= DisplayAvatar (mysql_result($Erg3, $Ui, "UID"));
      $Spalten.= "<br>";
    } //FOR 
    
    //wenn noch engel benötigt werden
    if( mysql_result($Erg2, 0, $Uj)-$Ui>0 ) {
    if ($Ui!=0) $Spalten.= Get_Text("inc_schicht_und");
    
    // Link zum Eintragen
    $Spalten.= "\n\t\t\t<a href='".$ENGEL_ROOT.
               "nonpublic/schichtplan_add.php?ausdatum=".$ausdatum.
	       "&newentry=".$SID.
	       "&newtype=".$Uj.
	       "'>";
    $Spalten.= (mysql_result($Erg2, 0, $Uj)-$Ui);
    
    // Wort: weiter(e)?
    if (mysql_num_rows($Erg3) >= 1) { 
      if ((mysql_result($Erg2, 0, $Uj)-$Ui) > 1) {
        $Spalten.= Get_Text("inc_schicht_weitere"); 
        } 
      else {
        $Spalten.= Get_Text("inc_schicht_weiterer");
        }
      }
    $Spalten.= " ".Get_Text("inc_schicht_Engel");
    // mehr als 1 weiterer Engel?
    if (mysql_result($Erg2, 0, $Uj)-$Ui!=1) 
      $Spalten.= Get_Text("inc_schicht_werden");
    else 
      $Spalten.= Get_Text("inc_schicht_wird");
    $Spalten.= Get_Text("inc_schicht_noch_gesucht");
    $Spalten.= "</a><br>";
    }
  }
}      
return $Spalten;
} // function Ausgabe_Feld_Inhalt


?>
<?php

function Ausgabe_Feld_Inhalt_Druck($RID, $SID) {
// gibt, nach übergabe der der SchichtID (SID) und der RaumID (RID),
// die eingetragenden und und offenden Schichteintäge zurück
  include ("./inc/db.php");
  include ("./inc/config.php");
  
  $SQL2 = "SELECT * FROM `Raeume` WHERE ";
  $SQL2.= "(RID = '".$RID."')";
  $Erg2 = mysql_query($SQL2, $con);
  for ($Uj = 2; $Uj < mysql_num_fields($Erg2); $Uj++) {
    if (mysql_result($Erg2, 0, $Uj)) {
    $Spalten.= "\n\t\t\t ".mysql_field_name($Erg2,
		$Uj)."engel:<br>&nbsp;&nbsp;";
          
    $SQL3 = "SELECT * FROM `Schichtbelegung` WHERE ";
    $SQL3.= "((SID = '".$SID."') AND";
    $SQL3.= " (Art = '".$Uj."'))";
    $Erg3 = mysql_query($SQL3, $con);
    
    // Ausgabe bisherige Engel fuer die Schicht
    for ($Ui = 0; $Ui < mysql_num_rows($Erg3); $Ui++) { 
      if ($Ui!=0) $Spalten.= ", ";
      elseif (mysql_num_rows($Erg3) == 1) $Spalten.= Get_Text("inc_schicht_ist");
      else $Spalten.= Get_Text("inc_schicht_sind");
      $Spalten.= UID2Nick(mysql_result($Erg3, $Ui, "UID"));
    } 
    
    //wenn noch engel benötigt werden
    if (mysql_result($Erg2, 0, $Uj)-$Ui) {
    if ($Ui!=0) $Spalten.= Get_Text("inc_schicht_und");
    
    $Spalten.= "\n\t\t\t";
    $Spalten.= (mysql_result($Erg2, 0, $Uj)-$Ui);
    
    // Wort: weiter(e)?
    if (mysql_num_rows($Erg3) >= 1) { 
      if ((mysql_result($Erg2, 0, $Uj)-$Ui) > 1) {
        $Spalten.= Get_Text("inc_schicht_weitere"); 
        } 
      else {
        $Spalten.= Get_Text("inc_schicht_weiterer");
        }
      }
    $Spalten.= " ".Get_Text("inc_schicht_Engel");
    // mehr als 1 weiterer Engel?
    if (mysql_result($Erg2, 0, $Uj)-$Ui!=1) 
      $Spalten.= Get_Text("inc_schicht_werden");
    else 
      $Spalten.= Get_Text("inc_schicht_wird");
    $Spalten.= Get_Text("inc_schicht_noch_gesucht");
    $Spalten.= "<br>";
    } else {
     $Spalten.="<br>";
    }
  }
}      
return $Spalten;
} // function Ausgabe_Feld_Inhalt


?>
