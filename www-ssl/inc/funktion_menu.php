<?PHP

function ShowMenu( $Menu )
{
	if( !isset($Menu["Entry"]) ) return;
	
	global $MenueTableStart, $MenueTableEnd, $_SESSION, $DEBUG;
	
	echo $MenueTableStart;
	echo "<h4 class=\"menu\">". $Menu["Name"]. "</h4>";

	foreach( $Menu["Entry"] as $Entry )
	{
		//wenn File mit ../ beginnt wird "../" abgeschnitten und der Ordener weggelassen
		if( strstr( $Entry["File"], "../" ) != FALSE )
			$MenuFile = substr( $Entry["File"], strpos( $Entry["File"], "../" )+ 3)  ;
		else
			$MenuFile = $Menu["Path"]. $Entry["File"];
	
		if( $_SESSION['CVS'][$MenuFile] == "Y")
			echo "\t\t\t<li><a href=\"". $Entry["File"]. "\">". $Entry["Name"]. "</a></li>\n";
		if( isset($Entry["Line"]))
			echo $Entry["Line"];


		//DEBUG
		if( $DEBUG ) 
		{ 
		    if( !isset($_SESSION['CVS'][$MenuFile] ) )
			echo "ERROR CVS: '". $MenuFile. "' not set";
			
		    if( $_SESSION['CVS'][$MenuFile] != "Y")
		    	echo "\t\t\t<li>". $Entry["File"]. " (". $Entry["Name"]. ")</li>\n";
		} // DEBUG
	} //foreach

	echo $MenueTableEnd;
} //function ShowMenue


?>
