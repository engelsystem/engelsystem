<?php

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
 */
function raw_output($output) {
  echo $output;
  die();
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
  if (DateTime::createFromFormat("Y-m-d", trim($input))) {
    return new ValidationResult(true, DateTime::createFromFormat("Y-m-d", trim($input))->getTimestamp());
  }
  if ($null_allowed) {
    return new ValidationResult(true, null);
  }
  
  error($error_message);
  return new ValidationResult(false, null);
}

/**
 * Gibt den gefilterten REQUEST Wert ohne Zeilenumbrüche zurück
 */
function strip_request_item($name) {
  return strip_item($_REQUEST[$name]);
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
function strip_request_item_nl($name) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+\n]{1,})/ui", '', strip_tags($_REQUEST[$name]));
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

class ValidationResult {

  private $valid;

  private $value;

  /**
   * Constructor.
   *
   * @param boolean $valid
   *          Is the value valid?
   * @param * $value
   *          The validated value
   */
  public function ValidationResult($valid, $value) {
    $this->valid = $valid;
    $this->value = $value;
  }

  /**
   * Is the value valid?
   */
  public function isValid() {
    return $this->valid;
  }

  /**
   * The parsed/validated value.
   */
  public function getValue() {
    return $this->value;
  }
}

?>
