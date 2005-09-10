<?php
//soll dein funktion entahlten die alle übergebenen parameter überprüft
//'`'" 
  
foreach ($_GET as $k => $v) 
{	
  	$v = htmlspecialchars($v);
//echo "$v<br>";
	$v = mysql_escape_string($v);
//echo "$v<br>";
//	$v = htmlentities($v);
//echo "$v<br>";
//    if (preg_match('/([\'"`\'])/', $v, $match)) 
	if (preg_match('/([\"`])/', $v, $match)) 
	{
		print "sorry get has illegal char '$match[1]'";
		exit;
	}
	$$k = $v;
}
  
foreach ($_POST as $k => $v) 
{
  	$v = htmlspecialchars($v);
//echo "$v<br>";
	$v = mysql_escape_string($v);
//echo "$v<br>";
//	$v = htmlentities($v);
//echo "$v<br>";
	if (preg_match('/([\'"`\'])/', $v, $match)) {
		print "sorry post has illegal char '$match[1]'";
		exit;
	}
	$$k = $v;
}

?>
