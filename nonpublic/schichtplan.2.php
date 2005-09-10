<h4>&nbsp;Raum übersicht</h4>

<?php

foreach( $Room as $RoomEntry  )
  echo "\t<li><a href='./schichtplan.php?ausdatum=$ausdatum&raum=". $RoomEntry["RID"]. "'>".
       $RoomEntry["Name"]. "</a></li>\n";

echo "<br>";
echo "<li><a href='./schichtplan.php?ausdatum=$ausdatum&raum=-1'>alle</a></li>";
?>

