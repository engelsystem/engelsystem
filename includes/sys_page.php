<?php

/**
 * Redirects to the URL passed on and stops the script .
 */
function redirect($to) {
  header("Location: " . $to, true, 302);
  die();
}

/**
 * Returns the filtered REQUEST value returned without line breaks
 */
function strip_request_item($name) {
  return strip_item($_REQUEST[$name]);
}

/**
 * Tests if the specified REQUEST value is an integer , or
 * an ID could be .
 */
function test_request_int($name) {
  if (isset($_REQUEST[$name]))
    return preg_match("/^[0-9]*$/", $_REQUEST[$name]);
  return false;
}

/**
 * Returns the filtered value REQUEST back with line breaks
 */
function strip_request_item_nl($name) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+\n]{1,})/ui", '', strip_tags($_REQUEST[$name]));
}

/**
 * Removes unwanted characters
 */
function strip_item($item) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+]{1,})/ui", '', strip_tags($item));
}

/**
 * Checks e- mail address .
 */
function check_email($email) {
  return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

?>