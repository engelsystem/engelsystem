<?php
function sql_connect($host, $user, $pw, $db) {
	global $con;
	global $host;

	@ $con = mysql_connect($host, $user, $pw);

	if ($con == null)
		die("no mysql-connection");

	if (!mysql_select_db($db, $con))
		die("mysql db-selection failed");

	mysql_query("SET CHARACTER SET utf8;", $con);
	mysql_query("SET NAMES 'utf8'", $con);
}

// Do select query
function sql_select($query) {
	global $con;
	$start = microtime(true);
	if ($result = mysql_query($query, $con)) {
		$data = array ();
		while ($line = mysql_fetch_assoc($result)) {
			array_push($data, $line);
		}
		return $data;
	} else {
		die('MySQL-query error: ' . $query . ", " . mysql_error($con));
	}
}

// Execute a query
function sql_query($query) {
	global $con;
	$start = microtime(true);
	if ($result = mysql_query($query, $con)) {
		return $result;
	} else {
		die('MySQL-query error: ' . $query . ", " . mysql_error($con));
	}
}

function sql_id() {
	global $con;
	return mysql_insert_id($con);
}

function sql_escape($query) {
	return mysql_real_escape_string($query);
}

function sql_num_query($query) {
	return mysql_num_rows(sql_query($query));
}

function sql_error() {
	global $con;
	return mysql_error($con);
}
?>
