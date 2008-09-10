<h4>&nbsp;Raum&uuml;bersicht</h4>
<?php

include ("../../includes/funktion_schichtplan_aray.php");

if( isset ($Room))
    foreach( $Room as $RoomEntry  )
    {
	if(isset($ausdatum))
	  echo "\t<li><a href='./schichtplan.php?ausdatum=$ausdatum&raum=". $RoomEntry["RID"]. "'>".
	       $RoomEntry["Name"]. "</a></li>\n";
	else
	  echo "\t<li><a href='./schichtplan.php?raum=". $RoomEntry["RID"]. "'>".
	       $RoomEntry["Name"]. "</a></li>\n";
    }
echo "<br>";
if(isset($ausdatum))
	echo "<li><a href='./schichtplan.php?ausdatum=$ausdatum&raum=-1'>alle</a></li>";
else
	echo "<li><a href='./schichtplan.php?raum=-1'>alle</a></li>";
?>

