<?php
//soll dein funktion entahlten die alle übergebenen parameter überprüft
//'`'" 

foreach ($_GET as $k => $v) 
{	
  	$v = htmlspecialchars($v);
	$v = mysql_escape_string($v);
//	$v = htmlentities($v);
	if (preg_match('/([\"`])/', $v, $match)) 
	{
		print "sorry get has illegal char '$match[1]'";
		exit;
	}
	$_GET[$k] = $v;
	echo "GET $k=\"$v\"<br>";
}
  
foreach ($_POST as $k => $v) 
{
  	$v = htmlspecialchars($v);
	$v = mysql_escape_string($v);
//	$v = htmlentities($v);
	if (preg_match('/([\'"`\'])/', $v, $match)) {
		print "sorry post has illegal char '$match[1]'";
		exit;
	}
	$_POST[$k] = $v;
	echo "POST $k=\"$v\"<br>";
}

?>
