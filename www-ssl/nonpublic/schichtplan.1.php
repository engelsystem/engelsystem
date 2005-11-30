<h4>&nbsp;Tage </h4>

<?PHP
include ("./inc/funktion_schichtplan_aray.php");

function Printlink( $Datum)
{
	GLOBAL $raum;
	echo "\t<li><a href='./schichtplan.php?ausdatum=$Datum";
	// ist ein raum gesetzt?
	if (IsSet($raum)) 
		echo "&raum=$raum";
	echo "'>$Datum</a></li>\n";
} //function Printlink(

foreach( $VeranstaltungsTage as $k => $v)
{
	Printlink( $v);
}

?>
