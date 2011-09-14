<?php
function guest_start() {
	header("Location: ?p=login");
	die();
}
?>