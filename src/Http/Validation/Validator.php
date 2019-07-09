<?php

namespace Engelsystem\Http\Validation;

use Illuminate\Support\Str;
use InvalidArgumentException;

class Validator
{
    /** @var Validates */
    protected $validate;

    /** @var string[] */
    protected $errors = [];

    /** @var array */
    protected $data = [];

    /**
     * @param Validates $validate
     */
    public function __construct(Validates $validate)
    {
        $this->validate = $validate;
    }

    /**
     * @param array $data
     * @param array $rules
     * @return bool
     */
    public function validate($data, $rules)
    {
        $this->errors = [];
        $this->data = [];

        foreach ($rules as $key => $values) {
            foreach (explode('|', $values) as $parameters) {
                $parameters = explode(':', $parameters);
                $rule = array_shift($parameters);
                $rule = Str::camel($rule);

                if (!method_exists($this->validate, $rule)) {
                    throw new InvalidArgumentException('Unknown validation rule: ' . $rule);
                }

                $value = isset($data[$key]) ? $data[$key] : null;
                if (!$this->validate->{$rule}($value, $parameters, $data)) {
                    $this->errors[$key][] = implode('.', ['validation', $key, $rule]);

                    continue;
                }

                $this->data[$key] = $value;
            }
        }

        return empty($this->errors);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
