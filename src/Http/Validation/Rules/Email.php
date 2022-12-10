<?php

namespace Engelsystem\Http\Validation\Rules;

use Egulias\EmailValidator\EmailValidator;
use Respect\Validation\Rules\Email as RespectEmail;

class Email extends RespectEmail
{
    // Fix for "Creation of dynamic property $emailValidator is deprecated" warning in PHP 8.2
    public ?EmailValidator $emailValidator;
}
