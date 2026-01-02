<?php

declare(strict_types=1);

namespace Engelsystem\Services;

/**
 * Result of validating whether a user can work a shift.
 * Contains success/failure status and detailed error messages.
 */
readonly class ShiftValidationResult
{
    /**
     * @param bool $isValid Whether the validation passed
     * @param string[] $errors List of validation error messages
     * @param string[] $warnings List of warning messages (non-blocking)
     */
    public function __construct(
        public bool $isValid,
        public array $errors = [],
        public array $warnings = []
    ) {
    }

    /**
     * Create a successful validation result.
     *
     * @param string[] $warnings Optional warnings
     */
    public static function success(array $warnings = []): self
    {
        return new self(true, [], $warnings);
    }

    /**
     * Create a failed validation result.
     *
     * @param string[] $errors
     * @param string[] $warnings
     */
    public static function failure(array $errors, array $warnings = []): self
    {
        return new self(false, $errors, $warnings);
    }

    /**
     * Create a failed validation result with a single error.
     */
    public static function error(string $error): self
    {
        return new self(false, [$error]);
    }

    /**
     * Check if there are any errors.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Check if there are any warnings.
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * Get all errors as a single string.
     */
    public function getErrorsAsString(string $separator = ', '): string
    {
        return implode($separator, $this->errors);
    }

    /**
     * Get all warnings as a single string.
     */
    public function getWarningsAsString(string $separator = ', '): string
    {
        return implode($separator, $this->warnings);
    }

    /**
     * Merge this result with another.
     * Result is valid only if both are valid.
     */
    public function merge(self $other): self
    {
        return new self(
            $this->isValid && $other->isValid,
            array_merge($this->errors, $other->errors),
            array_merge($this->warnings, $other->warnings)
        );
    }
}
