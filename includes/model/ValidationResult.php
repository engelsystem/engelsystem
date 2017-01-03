<?php

namespace Engelsystem;

/**
 * BO that represents the result of an entity attribute validation.
 * It contains the validated value and a bool for validation success.
 */
class ValidationResult
{
    /** @var bool */
    private $valid;

    /** @var mixed */
    private $value;

    /**
     * @param boolean $valid Is the value valid?
     * @param mixed   $value The validated value
     */
    public function __construct($valid, $value)
    {
        $this->valid = $valid;
        $this->value = $value;
    }

    /**
     * Is the value valid?
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * The parsed/validated value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
