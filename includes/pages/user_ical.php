<?php

/**
 * Controller for ical output of users own shifts or any user_shifts filter.
 */
function user_ical() {
  global $user;
  
  if (! isset($_REQUEST['key']) || ! preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key'])) {
    engelsystem_error("Missing key.");
  }
  $key = $_REQUEST['key'];
  
  $user = User_by_api_key($key);
  if ($user == null) {
    engelsystem_error("Key invalid.");
  }
  
  if (! in_array('ical', privileges_for_user($user['UID']))) {
    engelsystem_error("No privilege for ical.");
  }
  
  $ical_shifts = load_ical_shifts();
  
  send_ical_from_shifts($ical_shifts);
}

/**
 * Renders an ical calender from given shifts array.
 *
 * @param array<Shift> $shifts          
 */
function send_ical_from_shifts($shifts) {
  header("Content-Type: text/calendar; charset=utf-8");
  $output = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//-//Engelsystem//DE\r\nCALSCALE:GREGORIAN\r\n";
  foreach ($shifts as $shift) {
    $output .= make_ical_entry_from_shift($shift);
  }
  $output .= "END:VCALENDAR\r\n";
  $output = trim($output, "\x0A");
  header("Content-Length: " . strlen($output));
  raw_output($output);
}

/**
 * Renders an ical vevent from given shift.
 *
 * @param Shift $shift          
 */
function make_ical_entry_from_shift($shift) {
  $output = "BEGIN:VEVENT\r\n";
  $output .= "UID:" . md5($shift['start'] . $shift['end'] . $shift['name']) . "\r\n";
  $output .= "SUMMARY:" . str_replace("\n", "\\n", $shift['name']) . " (" . str_replace("\n", "\\n", $shift['title']) . ")\r\n";
  if (isset($shift['Comment'])) {
    $output .= "DESCRIPTION:" . str_replace("\n", "\\n", $shift['Comment']) . "\r\n";
  }
  $output .= "DTSTART;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['start']) . "\r\n";
  $output .= "DTEND;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['end']) . "\r\n";
  $output .= "LOCATION:" . $shift['Name'] . "\r\n";
  $output .= "END:VEVENT\r\n";
  return $output;
}
?>
