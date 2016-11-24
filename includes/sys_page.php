<?php
use Engelsystem\ValidationResult;

/**
 * Provide page/request helper functions
 */

/**
 * Parse a date from da day and a time textfield.
 *
 * @param string $date_name
 *          Name of the textfield containing the day (format Y-m-d)
 * @param string $time_name
 *          Name of the textfield containing the time (format H:i)
 * @param string[] $allowed_days
 *          List of allowed days in format Y-m-d
 * @param int $default_value
 *          Default value unix timestamp
 */
function check_request_datetime($date_name, $time_name, $allowed_days, $default_value) {
  $time = date("H:i", $default_value);
  $day = date("Y-m-d", $default_value);
  
  if (isset($_REQUEST[$time_name]) && preg_match('#^\d{1,2}:\d\d$#', trim($_REQUEST[$time_name]))) {
    $time = trim($_REQUEST[$time_name]);
  }
  if (isset($_REQUEST[$date_name]) && in_array($_REQUEST[$date_name], $allowed_days)) {
    $day = $_REQUEST[$date_name];
  }
  
  return parse_date("Y-m-d H:i", $day . " " . $time);
}

/**
 * Parse a date into unix timestamp
 *
 * @param string $pattern
 *          The date pattern (i.e. Y-m-d H:i)
 * @param string $value
 *          The string to parse
 * @return The parsed unix timestamp
 */
function parse_date($pattern, $value) {
  $datetime = DateTime::createFromFormat($pattern, trim($value));
  if ($datetime == null) {
    return null;
  }
  return $datetime->getTimestamp();
}

/**
 * Leitet den Browser an die übergebene URL weiter und hält das Script an.
 */
function redirect($url) {
  header("Location: " . $url, true, 302);
  raw_output("");
}

/**
 * Echoes given output and dies.
 *
 * @param String $output
 *          String to display
 */
function raw_output($output) {
  echo $output;
  die();
}

/**
 * Helper function for transforming list of entities into array for select boxes.
 *
 * @param array $data
 *          The data array
 * @param string $key_name
 *          name of the column to use as id/key
 * @param string $value_name
 *          name of the column to use as displayed value
 */
function select_array($data, $key_name, $value_name) {
  $ret = [];
  foreach ($data as $value) {
    $ret[$value[$key_name]] = $value[$value_name];
  }
  return $ret;
}

/**
 * Returns an int[] from given request param name.
 *
 * @param String $name
 *          Name of the request param
 * @param array<int> $default
 *          Default return value, if param is not set
 */
function check_request_int_array($name, $default = []) {
  if (isset($_REQUEST[$name]) && is_array($_REQUEST[$name])) {
    return array_filter($_REQUEST[$name], 'is_numeric');
  }
  return $default;
}

/**
 * Checks if given request item (name) can be parsed to a date.
 * If not parsable, given error message is put into msg() and null is returned.
 *
 * @param string $input
 *          String to be parsed into a date.
 * @param string $error_message
 *          the error message displayed if $input is not parsable
 * @param boolean $null_allowed
 *          is a null value allowed?
 * @return ValidationResult containing the parsed date
 */
function check_request_date($name, $error_message = null, $null_allowed = false) {
  if (! isset($_REQUEST[$name])) {
    return new ValidationResult($null_allowed, null);
  }
  return check_date($_REQUEST[$name], $error_message, $null_allowed);
}

/**
 * Checks if given string can be parsed to a date.
 * If not parsable, given error message is put into msg() and null is returned.
 *
 * @param string $input
 *          String to be parsed into a date.
 * @param string $error_message
 *          the error message displayed if $input is not parsable
 * @param boolean $null_allowed
 *          is a null value allowed?
 * @return ValidationResult containing the parsed date
 */
function check_date($input, $error_message = null, $null_allowed = false) {
  if ($tmp = parse_date("Y-m-d H:i", trim($input) . " 00:00")) {
    return new ValidationResult(true, $tmp);
  }
  if ($null_allowed) {
    return new ValidationResult(true, null);
  }
  
  error($error_message);
  return new ValidationResult(false, null);
}

/**
 * Returns REQUEST value filtered or default value (null) if not set.
 */
function strip_request_item($name, $default_value = null) {
  if (isset($_REQUEST[$name])) {
    return strip_item($_REQUEST[$name]);
  }
  return $default_value;
}

/**
 * Testet, ob der angegebene REQUEST Wert ein Integer ist, bzw.
 * eine ID sein könnte.
 */
function test_request_int($name) {
  if (isset($_REQUEST[$name])) {
    return preg_match("/^[0-9]*$/", $_REQUEST[$name]);
  }
  return false;
}

/**
 * Gibt den gefilterten REQUEST Wert mit Zeilenumbrüchen zurück
 */
function strip_request_item_nl($name, $default_value = null) {
  if (isset($_REQUEST[$name])) {
    return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+\n]{1,})/ui", '', strip_tags($_REQUEST[$name]));
  }
  return $default_value;
}

/**
 * Entfernt unerwünschte Zeichen
 */
function strip_item($item) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+]{1,})/ui", '', strip_tags($item));
}

/**
 * Überprüft eine E-Mail-Adresse.
 */
function check_email($email) {
  return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

?>
