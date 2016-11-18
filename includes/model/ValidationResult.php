<?php

namespace Engelsystem;

/**
 * BO that represents the result of an entity attribute validation.
 * It contains the validated value and a bool for validation success.
 */
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
  public function __construct($valid, $value) {
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