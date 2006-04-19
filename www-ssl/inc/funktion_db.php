<?PHP

if( !function_exists("db_query"))
{
	function Ausgabe_Daten($SQL)
	{
		global $con;	
	
	
		$Erg = mysql_query($SQL, $con);
		echo mysql_error($con);
		
		$Zeilen  = mysql_num_rows($Erg);
		$Anzahl_Felder = mysql_num_fields($Erg);
		
		$Diff  = "<table border=1>";
		$Diff .= "<tr>";
		for ($m = 0 ; $m < $Anzahl_Felder ; $m++)
			$Diff .= "<th>". mysql_field_name($Erg, $m). "</th>";
		$Diff .= "</tr>";
		for ($n = 0 ; $n < $Zeilen ; $n++) 
		{
			$Diff .= "<tr>";
		        for ($m = 0 ; $m < $Anzahl_Felder ; $m++)
  	  			$Diff .= "<td>".mysql_result($Erg, $n, $m). "</td>";
        		$Diff .= "</tr>";
		}
		$Diff .= "</table>";
		return $Diff;
	}

	function db_query( $SQL, $comment)
	{
		global $con, $Page;	
		$Diff = "";
		
		//commed anlyse udn daten sicherung
		if( strpos( "#$SQL", "UPDATE") > 0)
		{
			//Tabellen name ermitteln
			$Table_Start = strpos( $SQL, "`");
			$Table_End   = strpos( $SQL, "`", $Table_Start+1);
			$Table = substr( $SQL, $Table_Start, ($Table_End-$Table_Start+1));
			
			//SecureTest
			if( $Table_Start == 0 || $Table_End == 0) die("<h1>funktion_db ERROR SQL: '$SQL' nicht OK</h1>");
	
			//WHERE ermitteln
			$Where_Start = strpos( $SQL, "WHERE");
			$Where = substr( $SQL, $Where_Start);
			if( $Where_Start == 0)	$Where = ";"; 
		
			if( strlen( $Where) < 2) 	
			{
				$Diff = "can't show, too mutch data (no filter was set)";
				$querry_erg = mysql_query($SQL, $con);
			}
			else
			{
				$Diff .= Ausgabe_Daten( "SELECT * FROM $Table $Where");
				//execute command
				$querry_erg = mysql_query($SQL, $con);
				$Diff .= Ausgabe_Daten( "SELECT * FROM $Table $Where");
			}
		}
		elseif( strpos( "#$SQL", "DELETE") > 0)
		{
			$TableWhere = substr( $SQL, 6);
			$Diff .= Ausgabe_Daten( "SELECT * $TableWhere");

			//execute command
			$querry_erg = mysql_query($SQL, $con);
		}
		elseif( strpos( "#$SQL", "INSERT") > 0)
		{
			//execute command
			$querry_erg = mysql_query($SQL, $con);
		}
		else
		{
			//execute command
			$querry_erg = mysql_query($SQL, $con);
		}

		$SQLCommand = "SQL:<br>". htmlentities( $SQL, ENT_QUOTES);
		if( strlen($Diff) > 0)
			$SQLCommand .= "<br><br>Diff:<br>$Diff";

		$Commend = htmlentities( ($Page["Name"]. ": ". $comment), ENT_QUOTES);
		//LOG commands in DB
		$SQL_SEC =	"INSERT INTO `ChangeLog` ( `UID` , `SQLCommad` , `Commend` ) ".
				" VALUES ( '". $_SESSION['UID']. "', ".
					  "'". mysql_escape_string( $SQLCommand). "', ".
					  "'". mysql_escape_string( $Commend). "' );";
		$erg = mysql_query($SQL_SEC, $con);
		echo mysql_error($con);
		return $querry_erg;
	}//function db_query(
}

?>
