<?php

use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Support\Collection;

/**
 * Controller for ical output of users own shifts or any user_shifts filter.
 */
function user_ical()
{
    $user = auth()->userFromApi();

    if (!$user) {
        throw new HttpForbidden('Missing or invalid ?key=', ['content-type' => 'text/text']);
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
 * @param Shift[]|Collection $shifts Shift
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
 * @param Shift $shift
 * @return string
 */
function make_ical_entry_from_shift(Shift $shift)
{
    $output = "BEGIN:VEVENT\r\n";
    $output .= 'UID:' . md5($shift->start->timestamp . $shift->end->timestamp . $shift->shiftType->name) . "\r\n";
    $output .= 'SUMMARY:' . str_replace("\n", "\\n", $shift->shiftType->name)
        . ' (' . str_replace("\n", "\\n", $shift->title) . ")\r\n";
    if (isset($shift->user_comment)) {
        $output .= 'DESCRIPTION:' . str_replace("\n", "\\n", $shift->user_comment) . "\r\n";
    }
    $output .= 'DTSTAMP:' . $shift->start->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'DTSTART:' . $shift->start->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'DTEND:' . $shift->end->utc()->format('Ymd\THis\Z') . "\r\n";
    $output .= 'LOCATION:' . $shift->room->name . "\r\n";
    $output .= "END:VEVENT\r\n";
    return $output;
}
