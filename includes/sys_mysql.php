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
		print_r(debug_backtrace());
		die('MySQL-query error: ' . $query . ", " . mysql_error($con));
	}
}

function sql_select_single_col($query) {
	$result = sql_select($query);
	return array_map('array_shift', $result);
}

function sql_select_single_cell($query) {
	return array_shift(array_shift(sql_select($query)));
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

$sql_transaction_counter = 0;
function sql_start_transaction() {
	global $sql_transaction_counter;
	if ($sql_transaction_counter++ == 0)
		sql_query("START TRANSACTION");
}

function sql_stop_transaction() {
	global $sql_transaction_counter;
	if ($sql_transaction_counter-- == 1)
		sql_query("COMMIT");
}
?>
