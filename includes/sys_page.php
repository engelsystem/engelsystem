<?php

use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;
use Engelsystem\Models\BaseModel;
use Engelsystem\ValidationResult;
use Illuminate\Support\Collection;

/**
 * Provide page/request helper functions
 */

/**
 * Parse a date from da day and a time textfield.
 *
 * @param string   $date_name     Name of the textfield containing the day (format Y-m-d)
 * @param string   $time_name     Name of the textfield containing the time (format H:i)
 * @param string[] $allowed_days  List of allowed days in format Y-m-d
 * @param int      $default_value Default value unix timestamp
 * @return int|null
 */
function check_request_datetime($date_name, $time_name, $allowed_days, $default_value)
{
    $time = date('H:i', $default_value);
    $day = date('Y-m-d', $default_value);
    $request = request();

    if ($request->has($time_name) && preg_match('#^\d{1,2}:\d\d$#', trim($request->input($time_name)))) {
        $time = trim($request->input($time_name));
    }

    if ($request->has($date_name) && in_array($request->input($date_name), $allowed_days)) {
        $day = $request->input($date_name);
    }

    return parse_date('Y-m-d H:i', $day . ' ' . $time);
}

/**
 * Parse a date into unix timestamp
 *
 * @param string $pattern The date pattern (i.e. Y-m-d H:i)
 * @param string $value   The string to parse
 * @return int|null The parsed unix timestamp
 */
function parse_date($pattern, $value)
{
    $datetime = DateTime::createFromFormat($pattern, trim($value));
    if (!$datetime) {
        return null;
    }

    return $datetime->getTimestamp();
}

/**
 * Leitet den Browser an die übergebene URL weiter und hält das Script an.
 *
 * @param string $url
 */
function throw_redirect($url)
{
    throw new HttpTemporaryRedirect($url);
}

/**
 * Echoes given output and dies.
 *
 * @param String $output String to display
 */
function raw_output($output = '')
{
    echo $output;
    die();
}

/**
 * Helper function for transforming list of entities into array for select boxes.
 *
 * @param array|Collection $data       The data array
 * @param string           $key_name   name of the column to use as id/key
 * @param string           $value_name name of the column to use as displayed value
 *
 * @return array|Collection
 */
function select_array($data, $key_name, $value_name)
{
    if ($data instanceof Collection) {
        return $data->mapToDictionary(function (BaseModel $model) use ($key_name, $value_name) {
            return [$model->{$key_name} => $model->{$value_name}];
        });
    }

    $return = [];
    foreach ($data as $value) {
        $return[$value[$key_name]] = $value[$value_name];
    }
    return $return;
}

/**
 * Returns an int[] from given request param name.
 *
 * @param string $name    Name of the request param
 * @param array  $default Default return value, if param is not set
 * @return array
 */
function check_request_int_array($name, $default = [])
{
    $request = request();
    if ($request->has($name) && is_array($request->input($name))) {
        return array_filter($request->input($name), 'is_numeric');
    }
    return $default;
}

/**
 * Checks if given request item (name) can be parsed to a date.
 * If not parsable, given error message is put into msg() and null is returned.
 *
 * @param string $name          to be parsed into a date.
 * @param string $error_message the error message displayed if $input is not parsable
 * @param bool   $null_allowed  is a null value allowed?
 * @param bool   $time_allowed  is time allowed?
 * @return ValidationResult containing the parsed date
 */
function check_request_date($name, $error_message = null, $null_allowed = false, $time_allowed = false)
{
    $request = request();
    if (!$request->has($name)) {
        return new ValidationResult($null_allowed, null);
    }
    return check_date($request->input($name), $error_message, $null_allowed, $time_allowed);
}

/**
 * Checks if given string can be parsed to a date.
 * If not parsable, given error message is put into msg() and null is returned.
 *
 * @param string $input         String to be parsed into a date.
 * @param string $error_message the error message displayed if $input is not parsable
 * @param bool   $null_allowed  is a null value allowed?
 * @param bool   $time_allowed  is time allowed?
 * @return ValidationResult containing the parsed date
 */
function check_date($input, $error_message = null, $null_allowed = false, $time_allowed = false)
{
    $trimmed_input = trim((string) $input);

    try {
        if ($time_allowed) {
            $time = Carbon::createFromDatetime($trimmed_input);
        } else {
            $time = Carbon::createFromFormat('Y-m-d', $trimmed_input);
        }
    } catch (InvalidArgumentException $e) {
        $time = null;
    }

    if ($time) {
        return new ValidationResult(true, $time);
    }

    if ($null_allowed) {
        return new ValidationResult(true, null);
    }

    error($error_message);
    return new ValidationResult(false, null);
}

/**
 * Returns REQUEST value filtered or default value (null) if not set.
 *
 * @param string $name
 * @param string|null $default_value
 * @return mixed|null
 */
function strip_request_item($name, $default_value = null)
{
    $request = request();
    if ($request->has($name)) {
        return strip_item($request->input($name));
    }
    return $default_value;
}

/**
 * Returns REQUEST value or default value (null) if not set.
 *
 * @param string $name
 * @param string|null $default_value
 * @return mixed|null
 */
function strip_request_tags($name, $default_value = null)
{
    $request = request();
    if ($request->has($name)) {
        return strip_tags($request->input($name));
    }

    return $default_value;
}

/**
 * Testet, ob der angegebene REQUEST Wert ein Integer ist, bzw.
 * eine ID sein könnte.
 *
 * @param string $name
 * @return int|false
 */
function test_request_int($name)
{
    $input = request()->input($name);
    if (is_null($input)) {
        return false;
    }

    return preg_match('/^\d+$/', $input);
}

/**
 * Gibt den gefilterten REQUEST Wert mit Zeilenumbrüchen zurück
 *
 * @param string $name
 * @param mixed  $default_value
 * @return mixed
 */
function strip_request_item_nl($name, $default_value = null)
{
    $request = request();
    if ($request->has($name)) {
        // Only allow letters, symbols, punctuation, separators, numbers and newlines without html tags
        return preg_replace(
            "/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+\n]+)/ui",
            '',
            strip_tags($request->input($name))
        );
    }
    return $default_value;
}

/**
 * Entfernt unerwünschte Zeichen
 *
 * @param string $item
 * @return string
 */
function strip_item($item)
{
    // Only allow letters, symbols, punctuation, separators and numbers without html tags
    return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+]+)/ui", '', strip_tags($item));
}

/**
 * Validates an email address with support for IDN domain names.
 *
 * @param string $email
 * @return bool
 */
function check_email($email)
{
    // Convert the domain part from idn to ascii
    if(substr_count($email, '@') == 1) {
        list($name, $domain) = explode('@', $email);
        $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        $email = $name . '@' . $domain;
    }
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}
