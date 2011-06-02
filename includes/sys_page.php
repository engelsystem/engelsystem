<?php

function strip_request_item($name) {
	return preg_replace(
		"/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui",
		'',
		strip_tags($_REQUEST[$name])
	);
}

function error($msg) {
	return '<p class="error">' . $msg . '</p>';
}

function success($msg) {
	return '<p class="success">' . $msg . '</p>';
}
?>
