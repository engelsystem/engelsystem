<?php

namespace Engelsystem\Http\Validation;

use InvalidArgumentException;

class Validates
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function accepted($value): bool
    {
        return in_array($value, ['true', '1', 'y', 'yes', 'on', 1, true], true);
    }

    /**
     * @param string $value
     * @param array  $parameters ['min', 'max']
     * @return bool
     */
    public function between($value, $parameters): bool
    {
        $this->validateParameterCount(2, $parameters, __FUNCTION__);
        $size = $this->getSize($value);

        return $size >= $parameters[0] && $size <= $parameters[1];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function bool($value): bool
    {
        return in_array($value, ['1', 1, true, '0', 0, false], true);
    }

    /**
     * @param mixed $value
     * @param array $parameters ['1,2,3,56,7']
     * @return bool
     */
    public function in($value, $parameters): bool
    {
        $this->validateParameterCount(1, $parameters, __FUNCTION__);

        return in_array($value, explode(',', $parameters[0]));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function int($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @param string $value
     * @param array  $parameters ['max']
     * @return bool
     */
    public function max($value, $parameters): bool
    {
        $this->validateParameterCount(1, $parameters, __FUNCTION__);
        $size = $this->getSize($value);

        return $size <= $parameters[0];
    }

    /**
     * @param string $value
     * @param array  $parameters ['min']
     * @return bool
     */
    public function min($value, $parameters)
    {
        $this->validateParameterCount(1, $parameters, __FUNCTION__);
        $size = $this->getSize($value);

        return $size >= $parameters[0];
    }

    /**
     * @param mixed $value
     * @param array $parameters ['1,2,3,56,7']
     * @return bool
     */
    public function notIn($value, $parameters): bool
    {
        $this->validateParameterCount(1, $parameters, __FUNCTION__);

        return !$this->in($value, $parameters);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function numeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function required($value): bool
    {
        if (
            is_null($value)
            || (is_string($value) && trim($value) === '')
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $value
     * @return int|float
     */
    protected function getSize($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return mb_strlen($value);
    }

    /**
     * @param int    $count
     * @param array  $parameters
     * @param string $rule
     *
     * @throws InvalidArgumentException
     */
    protected function validateParameterCount(int $count, array $parameters, string $rule)
    {
        if (count($parameters) < $count) {
            throw new InvalidArgumentException(sprintf(
                'The rule "%s" requires at least %d parameters',
                $rule,
                $count
            ));
        }
    }
}
