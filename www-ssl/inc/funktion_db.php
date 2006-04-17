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
		global $con;	
		
		//commed anlyse udn daten sicherung
		$Diff = "";
		if( strpos( "#$SQL", "UPDATE") > 0)
		{
			//Tabellen name ermitteln
			$Table_Start = strpos( $SQL, "`");
			$Table_End   = strpos( $SQL, "`", $Table_Start+1);
			$Table = substr( $SQL, $Table_Start, ($Table_End-$Table_Start+1));
	
			//WHERE ermitteln
			$Where_Start = strpos( $SQL, "WHERE");
			$Where = substr( $SQL, $Where_Start);
			
			// sicherheitsprüfung !!!!
			if( $Where_Start == 0) die("<h1>DIE: kein WHERE im SQL ausdruck gefunden</h1>");

			//Daten auslesen
			$Diff .= Ausgabe_Daten( "SELECT * FROM $Table $Where");

			//execute command
			$querry_erg = mysql_query($SQL, $con);
			
			//Daten auslesen
			$Diff .= Ausgabe_Daten( "SELECT * FROM $Table $Where");
		}
		elseif( strpos( "#$SQL", "DELETE") > 0)
		{
			$TableWhere = substr( $SQL, 6);
			
			//Daten auslesen
			$Diff .= Ausgabe_Daten( "SELECT * $TableWhere");

			//execute command
			$querry_erg = mysql_query($SQL, $con);
		}
		elseif( strpos( "#$SQL", "INSERT") > 0)
		{
			echo "##### LOG: INSERT #####";
		}
		else
		{
			//execute command
			$querry_erg = mysql_query($SQL, $con);
		}

		//LOG commands in DB
		$SQL_SEC =	"INSERT INTO `ChangeLog` ( `UID` , `SQLCommad` , `Commend` ) ".
				" VALUES ( ".
					"'". $_SESSION['UID']. "', ".
					"'SQL:<br>". htmlentities( $SQL, ENT_QUOTES). "<br><br>".
					 "Diff:<br>$Diff', ".
					"'". htmlentities( $comment, ENT_QUOTES). "' );";
		$erg = mysql_query($SQL_SEC, $con);
		echo mysql_error($con);
		
		
		return $querry_erg;
	}//function db_query(
}

?>
