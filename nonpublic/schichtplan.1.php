<h4>&nbsp;Tage </h4>

<?

function DatumUm1TagErhoehen( $Datum)
{
	$Jahr  = substr( $Datum, 0, 4);
	$Monat = substr( $Datum, 5, 2);
	$Tag   = substr( $Datum, 8, 2);

	$Tag++;
	
	switch( $Monat)
	{
		case 1:		$Mmax=31;	break;
		case 2:		$Mmax=28;       break;
		case 3:		$Mmax=31;       break;
		case 4:		$Mmax=30;       break;
		case 5:		$Mmax=31;       break;
		case 6:		$Mmax=30;       break;
		case 7:		$Mmax=31;       break;
		case 8:		$Mmax=31;       break;
		case 9:		$Mmax=30;       break;
		case 10:	$Mmax=31;       break;
		case 11:	$Mmax=30;       break;
		case 12:	$Mmax=31;       break;
	}

	if( $Tag > $Mmax)
	{
		$Tag = 1;
		$Monat++;
	}

	if( $Monat > 12 ) 
	{
		$Monat = 1;
		$Jahr++;
	}

	return ("$Jahr-$Monat-$Tag");
} //function DatumUm1Tagerhoehen(

function Printlink( $Datum)
{
	GLOBAL $raum;
	echo "\t<li><a href='./schichtplan.php?ausdatum=$Datum";
	// ist ein raum gesetzt?
	if (IsSet($raum)) 
		echo "&raum=$raum";
	echo "'>$Datum</a></li>\n";
} //function Printlink(

//suchen den ersten eintrags
$SQL = "SELECT `DateS` FROM `Shifts` ORDER BY `DateS` LIMIT 1";
$Erg = mysql_query($SQL, $con);

do
{
	//Startdatum einlesen und link ausgeben
	$DateS = substr(mysql_result($Erg, 0 , 0), 0,10);
	Printlink( $DateS);
	
	//auslesen den endes und eventuelle weitere tage ausgeben
	$SQL2 = "SELECT MAX(`DateE`) FROM `Shifts` ".
		"WHERE ( (`DateS` like '$DateS%') AND NOT (`DateE` like '%00:00:00'))";
	$Erg2 = mysql_query($SQL2, $con);
	$DateE = substr(mysql_result($Erg2, 0 , 0), 0,10);
	
	while( $DateS != $DateE)
	{
		$DateS = DatumUm1TagErhoehen( $DateS);
		Printlink( $DateS);
	}

	//suchen den nästen eintrag
	$SQL = "SELECT `DateS` FROM `Shifts` ".
		"WHERE (`DateS` > '$DateE 23:59:59' ) ".
		"ORDER BY `DateS` ".
		"LIMIT 1";
	$Erg = mysql_query($SQL, $con);
} while( mysql_fetch_row($Erg) > 0);

?>
