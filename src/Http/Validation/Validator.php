<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation;

use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionProperty;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Factory;
use Respect\Validation\Validator as RespectValidator;

class Validator
{
    /** @var string[] */
    protected array $errors = [];

    protected array $data = [];

    protected array $mapping = [
        'accepted' => 'Checked',
        'int'      => 'IntVal',
        'float'    => 'FloatVal',
        'required' => 'NotEmpty',
        'optional' => 'nullable',
    ];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->data = [];

        $this->configureValidationFactory();

        $validData = [];
        foreach ($rules as $fieldName => $rulesList) {
            $value = $data[$fieldName] ?? null;
            $rulesList = is_array($rulesList) ? $rulesList : explode('|', $rulesList);

            // Configure the check to be run for every rule
            foreach ($rulesList as $parameters) {
                $v = new RespectValidator();

                $parameters = is_array($parameters) ? $parameters : explode(':', $parameters);
                $rule = array_shift($parameters);
                $rule = Str::camel($rule);
                $rule = $this->map($rule);

                // Handle empty/optional values
                if ($rule == 'nullable') {
                    if (is_null($value) || $value === '') {
                        $validData[$fieldName] = null;
                        break;
                    }

                    $validData[$fieldName] = $value;
                    continue;
                }

                // Configure the validation
                try {
                    $v = call_user_func_array([$v, $rule], $parameters);
                } catch (ComponentException $e) {
                    throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
                }

                // Run validation
                if ($v->validate($value)) {
                    $validData[$fieldName] = $value;
                } else {
                    $this->errors[$fieldName][] = implode('.', ['validation', $fieldName, $this->mapBack($rule)]);
                }
            }
        }

        $success = empty($this->errors);
        if ($success) {
            $this->data = $validData;
        }

        return $success;
    }

    protected function map(string $rule): string
    {
        return $this->mapping[$rule] ?? $rule;
    }

    protected function mapBack(string $rule): string
    {
        $mapping = array_flip($this->mapping);

        return $mapping[$rule] ?? $rule;
    }

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

    public function addErrors(array $errors): self
    {
        $this->errors = array_merge($this->errors, $errors);

        return $this;
    }

    protected function configureValidationFactory(): void
    {
        $f = (new Factory())
            ->withRuleNamespace('\\Engelsystem\\Http\\Validation\\Rules');

        // Hacking around, alternative is to reimplement it...
        $property = new ReflectionProperty($f, 'rulesNamespaces');
        $property->setValue($f, array_reverse($property->getValue($f)));

        Factory::setDefaultInstance($f);
    }
}
