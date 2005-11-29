<?php
$title = "Himmel";
$header = "";

include ("./inc/header.php");
include ("./inc/funktion_user.php");

If( !isset($_GET["action"]) ) 
	$_GET["action"] = "start";

switch( $_GET["action"])
{
	case "start":
		echo Get_Text("Hello"). $_SESSION['Nick']. ", <br>\n";
		echo Get_Text("pub_messages_text1"). "<br><br>\n";
	
		//#####################
		//show exist Messages
		//#####################
		$SQL = "SELECT * FROM `Messages` WHERE `SUID`=". $_SESSION["UID"]. " OR `RUID`=". $_SESSION["UID"];
		$erg = mysql_query($SQL, $con);
	
		echo "<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
		echo "<tr>\n";
		echo "\t<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Datum"). "</b></td>\n";
		echo "\t<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Von"). "</b></td>\n";
		echo "\t<td class=\"contenttopic\"><b>". Get_Text("pub_messages_An"). "</b></td>\n";
		echo "\t<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Text"). "</b></td>\n";
		echo "\t<td class=\"contenttopic\"></td>\n";
		echo "</tr>\n";
		
		for( $i=0; $i<mysql_num_rows( $erg ); $i++ )
		{
			echo "<tr class=\"content\">\n";
			echo "\t<td>". mysql_result( $erg, $i, "Datum" ). "</td>\n";
			echo "\t<td>". UID2Nick( mysql_result( $erg, $i, "SUID" )). "</td>\n";
			echo "\t<td>". UID2Nick( mysql_result( $erg, $i, "RUID" )). "</td>\n";
			echo "\t<td>". mysql_result( $erg, $i, "Text" ). "</td>\n";
			echo "\t<td>"; 
					
			if( mysql_result( $erg, $i, "RUID")==$_SESSION["UID"])
			{
				echo "<a href=\"?action=DelMsg&Datum=". mysql_result( $erg, $i, "Datum" ). 
					"\">". Get_Text("pub_messages_DelMsg"). "</a>";
				if( mysql_result( $erg, $i, "isRead")=="N")
					echo "<br><br><a href=\"?action=MarkRead&Datum=". mysql_result( $erg, $i, "Datum" ). 
						"\">". Get_Text("pub_messages_MarkRead"). "</a>";
			}
			echo "</td>\n";
			echo "</tr>\n";
		}
		
		//#####################
		//send Messeges
		//#####################
		echo "<form action=\"". $_SERVER['SCRIPT_NAME']. "?action=SendMsg\" method=\"POST\" >";
		echo "<tr class=\"content\">\n";
		echo "\t<td></td>\n";
		echo "\t<td></td>\n";
		// Listet alle Nicks auf
		echo "\t<td><select name=\"RUID\">\n";
			$usql="select * from User order by Nick";
			$uErg = mysql_query($usql, $con);
			$urowcount = mysql_num_rows($uErg);
			for ($k=0; $k<$urowcount; $k++)
			{
				echo "\t\t\t<option value=\"".mysql_result($uErg, $k, "UID")."\">".
					mysql_result($uErg, $k, "Nick"). "</option>\n";
			}
		echo "</select></td>\n";
		echo "\t<td><textarea name=\"Text\"  cols=\"30\" rows=\"10\"></textarea></td>\n";
		echo "\t<td><input type=\"submit\" value=\"". Get_Text("save"). "\"></td>\n";
		echo "</tr>\n";
		echo "</form>";
		
		echo "</table>\n";
		break;
		
	case "SendMsg":
		echo Get_Text("pub_messages_Send1"). "...<br>\n";
		
		$SQL = "INSERT INTO `Messages` ( `Datum` , `SUID` , `RUID` , `Text` ) VALUES (".
			"'". gmdate("Y-m-j H:i:s", time()). "', ".
			"'". $_SESSION["UID"]. "', ".
			"'". $_POST["RUID"]."', ".
			"'". $_POST["Text"]. "');";
		
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) 
			echo Get_Text("pub_messages_Send_OK"). "\n";
		else 
			echo Get_Text("pub_messages_Send_Error"). "...\n(". mysql_error($con). ")";
		break;
	
	case "MarkRead":
		$SQL = "UPDATE `Messages` SET `isRead` = 'Y' ".
			"WHERE `Datum` = '". $_GET["Datum"]. "' AND `SUID`=". $_SESSION["UID"]. " ".
			"LIMIT 1 ;";
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) 
			echo Get_Text("pub_messages_MarkRead_OK"). "\n";
		else 
			echo Get_Text("pub_messages_MarkRead_KO"). "...\n(". mysql_error($con). ")";
		break;
	
	case "DelMsg":
		$SQL = "DELETE FROM `Messages` ".
			"WHERE `Datum` = '". $_GET["Datum"]. "' AND `RUID` = ". $_SESSION["UID"]. " ".
			"LIMIT 1;";
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) 
			echo Get_Text("pub_messages_DelMsg_OK"). "\n";
		else 
			echo Get_Text("pub_messages_DelMsg_KO"). "...\n(". mysql_error($con). ")";
		break;
		
	default:
		echo Get_Text("pub_messages_NoCommand");
}

include ("./inc/footer.php");
?>
