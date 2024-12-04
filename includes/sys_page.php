<?php

use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;

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
 * Send a JSON response.
 *
 * @param array $data
 */
function json_output(array $data): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    die();
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
    return preg_replace('/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+]+)/ui', '', strip_tags($item));
}
