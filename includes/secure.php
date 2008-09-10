<?php
//soll dein funktion entahlten die alle übergebenen parameter überprüft
//'`'" 

if( $DEBUG)
	echo "secure.php START<br>\n";

foreach ($_GET as $k => $v) 
{	
//  	$v = htmlspecialchars($v, ENT_QUOTES);
//	$v = mysql_escape_string($v);
	$v = htmlentities($v, ENT_QUOTES);
	if (preg_match('/([\'"`\'])/', $v, $match)) 
	{
		print "sorry get has illegal char '$match[1]'";
		exit;
	}
	$_GET[$k] = $v;

	if( $DEBUG)
		echo "GET $k=\"$v\"<br>";
}
  
foreach ($_POST as $k => $v) 
{
//  	$v = htmlspecialchars($v, ENT_QUOTES);
//	$v = mysql_escape_string($v);
	$v = htmlentities($v, ENT_QUOTES);
	if (preg_match('/([\'"`\'])/', $v, $match)) {
		print "sorry post has illegal char '$match[1]'";
		exit;
	}
	$_POST[$k] = $v;
	
	if( $DEBUG)
		echo "POST $k=\"$v\"<br>";
}
if( $DEBUG)
	echo "secure.php END<br>\n";

?>
