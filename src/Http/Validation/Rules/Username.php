<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;
use RuntimeException;

/**
 * Username validation.
 * Usernames must have 1-24 chars and NOT match the regular expression defined under the config key "username_regex".
 */
class Username extends AbstractRule
{
    public function validate(mixed $input): bool
    {
        $regex = config('username_regex');

        if ($regex === null) {
            throw new RuntimeException('username_regex not set in config');
        }

        return Validator::length(1, 24)->validate($input)
            && Validator::not(Validator::regex($regex))->validate($input);
    }
}
