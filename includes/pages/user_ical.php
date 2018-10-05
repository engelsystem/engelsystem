<?php

/**
 * Controller for ical output of users own shifts or any user_shifts filter.
 */
function user_ical()
{
    global $user;
    $request = request();

    if (!$request->has('key') || !preg_match('/^[\da-f]{32}$/', $request->input('key'))) {
        engelsystem_error('Missing key.');
    }
    $key = $request->input('key');

    $user = User_by_api_key($key);
    if (empty($user)) {
        engelsystem_error('Key invalid.');
    }

    if (!in_array('ical', privileges_for_user($user['UID']))) {
        engelsystem_error('No privilege for ical.');
    }

    $ical_shifts = load_ical_shifts();

    send_ical_from_shifts($ical_shifts);
}

/**
 * Renders an ical calendar from given shifts array.
 *
 * @param array $shifts Shift
 */
function send_ical_from_shifts($shifts)
{
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=shifts.ics');
    $output = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//-//" . config('app_name') . "//DE\r\nCALSCALE:GREGORIAN\r\n";
    foreach ($shifts as $shift) {
        $output .= make_ical_entry_from_shift($shift);
    }
    $output .= "END:VCALENDAR\r\n";
    $output = trim($output, "\x0A");
    header('Content-Length: ' . strlen($output));
    raw_output($output);
}

/**
 * Renders an ical vevent from given shift.
 *
 * @param array $shift
 * @return string
 */
function make_ical_entry_from_shift($shift)
{
    $output = "BEGIN:VEVENT\r\n";
    $output .= 'UID:' . md5($shift['start'] . $shift['end'] . $shift['name']) . "\r\n";
    $output .= 'SUMMARY:' . str_replace("\n", "\\n", $shift['name'])
        . ' (' . str_replace("\n", "\\n", $shift['title']) . ")\r\n";
    if (isset($shift['Comment'])) {
        $output .= 'DESCRIPTION:' . str_replace("\n", "\\n", $shift['Comment']) . "\r\n";
    }
    $output .= 'DTSTART;TZID=Europe/Berlin:' . date("Ymd\THis", $shift['start']) . "\r\n";
    $output .= 'DTEND;TZID=Europe/Berlin:' . date("Ymd\THis", $shift['end']) . "\r\n";
    $output .= 'LOCATION:' . $shift['Name'] . "\r\n";
    $output .= "END:VEVENT\r\n";
    return $output;
}
