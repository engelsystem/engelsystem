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

    /** @var array */
    protected $nestedRules = ['optional', 'not'];

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
            $values = explode('|', $values);

            $packing = [];
            foreach ($this->nestedRules as $rule) {
                if (in_array($rule, $values)) {
                    $packing[] = $rule;
                }
            }

            $values = array_diff($values, $this->nestedRules);
            foreach ($values as $parameters) {
                $parameters = explode(':', $parameters);
                $rule = array_shift($parameters);
                $rule = Str::camel($rule);
                $rule = $this->map($rule);

                // To allow rules nesting
                $w = $v;
                try {
                    foreach (array_reverse(array_merge($packing, [$rule])) as $rule) {
                        if (!in_array($rule, $this->nestedRules)) {
                            call_user_func_array([$w, $rule], $parameters);
                            continue;
                        }

                        $w = call_user_func_array([new RespectValidator(), $rule], [$w]);
                    }
                } catch (ComponentException $e) {
                    throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
                }

                if ($w->validate($value)) {
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
