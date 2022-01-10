<?php

use Carbon\Carbon;
use Engelsystem\Http\Exceptions\HttpForbidden;

/**
 * Controller for ical output of users own shifts or any user_shifts filter.
 */
function user_ical()
{
    $request = request();
    $user = auth()->apiUser('key');

    if (
        !$request->has('key')
        || !preg_match('/^[\da-f]{32}$/', $request->input('key'))
        || !$user
    ) {
        throw new HttpForbidden('Missing or invalid key', ['content-type' => 'text/text']);
    }

    if (!auth()->can('ical')) {
        throw new HttpForbidden('Not allowed', ['content-type' => 'text/text']);
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
    $start = Carbon::createFromTimestamp($shift['start']);
    $end = Carbon::createFromTimestamp($shift['end']);

    $output = "BEGIN:VEVENT\r\n";
    $output .= 'UID:' . md5($shift['start'] . $shift['end'] . $shift['name']) . "\r\n";
    $output .= 'SUMMARY:' . str_replace("\n", "\\n", $shift['name'])
        . ' (' . str_replace("\n", "\\n", $shift['title']) . ")\r\n";
    if (isset($shift['Comment'])) {
        $output .= 'DESCRIPTION:' . str_replace("\n", "\\n", $shift['Comment']) . "\r\n";
    }
    $output .= 'DTSTAMP:' . $start->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'DTSTART:' . $start->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'DTEND:' . $end->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'LOCATION:' . $shift['Name'] . "\r\n";
    $output .= "END:VEVENT\r\n";
    return $output;
}
