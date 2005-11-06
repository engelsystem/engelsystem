<?php

/* Schichtverplanung im Adminbereich */
function Dsubstr($DateString,$re) {
  if ($re==1)
    return substr($DateString, 0, 2);
  elseif ($re==2)
    return substr($DateString, 3, 2);
  else
    return substr($DateString, 6, 4);
}

/* Schichtverplanung im Engelbereich */

function engeldate($edate,$m) {
	if ($m==t)
    return substr($edate, 8, 2);
  elseif ($m==m)
    return substr($edate, 5, 2);
  elseif ($m==u)
    return substr($edate, 11, 5);
  else
    return substr($edate, 0, 4);
}

?>
