<?php

namespace Engelsystem\Http\Validation;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Validator as RespectValidator;

class Validator
{
    /** @var string[] */
    protected $errors = [];

    /** @var array */
    protected $data = [];

    /** @var array */
    protected $mapping = [
        'accepted' => 'TrueVal',
        'int'      => 'IntVal',
        'required' => 'NotEmpty',
    ];

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
            $v = new RespectValidator();
            $v->with('\\Engelsystem\\Http\\Validation\\Rules', true);

            $value = isset($data[$key]) ? $data[$key] : null;

            foreach (explode('|', $values) as $parameters) {
                $parameters = explode(':', $parameters);
                $rule = array_shift($parameters);
                $rule = Str::camel($rule);
                $rule = $this->map($rule);

                try {
                    call_user_func_array([$v, $rule], $parameters);
                } catch (ComponentException $e) {
                    throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
                }

                if ($v->validate($value)) {
                    $this->data[$key] = $value;
                } else {
                    $this->errors[$key][] = implode('.', ['validation', $key, $this->mapBack($rule)]);
                }

                $v->removeRules();
            }
        }

        return empty($this->errors);
    }

    /**
     * @param string $rule
     * @return string
     */
    protected function map($rule)
    {
        return $this->mapping[$rule] ?? $rule;
    }

    /**
     * @param string $rule
     * @return string
     */
    protected function mapBack($rule)
    {
        $mapping = array_flip($this->mapping);

        return $mapping[$rule] ?? $rule;
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
