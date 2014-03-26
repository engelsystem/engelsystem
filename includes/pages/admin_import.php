<?php
function admin_import_title() {
  return _("Pentabarf import");
}

function admin_import() {
  global $rooms_import;
  global $user;
  $html = "";
  
  $step = "input";
  if (isset($_REQUEST['step']))
    $step = $_REQUEST['step'];
  
  $html .= '<p>';
  $html .= $step == "input" ? '<b>1. Input</b>' : '1. Input';
  $html .= ' &raquo; ';
  $html .= $step == "check" ? '<b>2. Validate</b>' : '2. Validate';
  $html .= ' &raquo; ';
  $html .= $step == "import" ? '<b>3. Import</b>' : '3. Import';
  $html .= '</p>';
  
  $import_file = '../import/import_' . $user['UID'] . '.xml';
  
  switch ($step) {
    case "input":
      $ok = false;
      if ($test_handle = fopen('../import/tmp', 'w')) {
        fclose($test_handle);
        unlink('../import/tmp');
      } else {
        error("Webserver has no write-permission on import directory.");
      }
      
      if (isset($_REQUEST['submit'])) {
        $ok = true;
        if (isset($_FILES['xcal_file']) && ($_FILES['xcal_file']['error'] == 0)) {
          if (move_uploaded_file($_FILES['xcal_file']['tmp_name'], $import_file)) {
            libxml_use_internal_errors(true);
            if (simplexml_load_file($import_file) === false) {
              $ok = false;
              error("No valid xml/xcal file provided.");
              unlink($import_file);
            }
          } else {
            $ok = false;
            error("File upload went wrong.");
          }
        } else {
          $ok = false;
          error("Please provide some data.");
        }
      }
      
      if ($ok)
        redirect(page_link_to('admin_import') . "&step=check");
      else {
        $html .= form(array(
            form_info('', _("This import will create/update/delete rooms and shifts by given FRAB-export file. The needed file format is xcal.")),
            form_file('xcal_file', _("xcal-File (.xcal)")),
            form_submit('submit', _("Import")) 
        ));
      }
      break;
    
    case "check":
      if (! file_exists($import_file))
        redirect(page_link_to('admin_import'));
      
      list($rooms_new, $rooms_deleted) = prepare_rooms($import_file);
      list($events_new, $events_updated, $events_deleted) = prepare_events($import_file);
      
      $html .= form(array(
          '<h3>' . _("Rooms to create") . '</h3>',
          table(_("Name"), $rooms_new),
          '<h3>' . _("Rooms to delete") . '</h3>',
          table(_("Name"), $rooms_deleted),
          '<h3>' . _("Shifts to create") . '</h3>',
          table(array(
              'day' => _("Day"),
              'start' => _("Start"),
              'end' => _("End"),
              'name' => _("Name"),
              'room' => _("Room") 
          ), shifts_printable($events_new)),
          '<h3>' . _("Shifts to update") . '</h3>',
          table(array(
              'day' => _("Day"),
              'start' => _("Start"),
              'end' => _("End"),
              'name' => _("Name"),
              'room' => _("Room") 
          ), shifts_printable($events_updated)),
          '<h3>' . _("Shifts to delete") . '</h3>',
          table(array(
              'day' => _("Day"),
              'start' => _("Start"),
              'end' => _("End"),
              'name' => _("Name"),
              'room' => _("Room") 
          ), shifts_printable($events_deleted)),
          form_submit('submit', _("Import")) 
      ), page_link_to('admin_import') . '&step=import');
      break;
    
    case "import":
      if (! file_exists($import_file))
        redirect(page_link_to('admin_import'));
      
      list($rooms_new, $rooms_deleted) = prepare_rooms($import_file);
      foreach ($rooms_new as $room) {
        sql_query("INSERT INTO `Room` SET `Name`='" . sql_escape($room) . "', `FromPentabarf`='Y', `Show`='Y'");
        $rooms_import[trim($room)] = sql_id();
      }
      foreach ($rooms_deleted as $room)
        sql_query("DELETE FROM `Room` WHERE `Name`='" . sql_escape($room) . "' LIMIT 1");
      
      list($events_new, $events_updated, $events_deleted) = prepare_events($import_file);
      foreach ($events_new as $event)
        sql_query("INSERT INTO `Shifts` SET `name`='" . sql_escape($event['name']) . "', `start`=" . sql_escape($event['start']) . ", `end`=" . sql_escape($event['end']) . ", `RID`=" . sql_escape($event['RID']) . ", `PSID`=" . sql_escape($event['PSID']) . ", `URL`='" . sql_escape($event['URL']) . "'");
      
      foreach ($events_updated as $event)
        sql_query("UPDATE `Shifts` SET `name`='" . sql_escape($event['name']) . "', `start`=" . sql_escape($event['start']) . ", `end`=" . sql_escape($event['end']) . ", `RID`=" . sql_escape($event['RID']) . ", `PSID`=" . sql_escape($event['PSID']) . ", `URL`='" . sql_escape($event['URL']) . "' WHERE `PSID`=" . sql_escape($event['PSID']) . " LIMIT 1");
      
      foreach ($events_deleted as $event)
        sql_query("DELETE FROM `Shifts` WHERE `PSID`=" . sql_escape($event['PSID']) . " LIMIT 1");
      
      engelsystem_log("Pentabarf import done");
      
      unlink($import_file);
      
      $html .= success(_("It's done!"), true);
      break;
    default:
      redirect(page_link_to('admin_import'));
  }
  
  return $html;
}

function prepare_rooms($file) {
  global $rooms_import;
  $data = read_xml($file);
  
  // Load rooms from db for compare with input
  $rooms = sql_select("SELECT * FROM `Room` WHERE `FromPentabarf`='Y'");
  $rooms_db = array();
  $rooms_import = array();
  foreach ($rooms as $room) {
    $rooms_db[] = (string) $room['Name'];
    $rooms_import[$room['Name']] = $room['RID'];
  }
  
  $events = $data->vcalendar->vevent;
  $rooms_pb = array();
  foreach ($events as $event) {
    $rooms_pb[] = (string) $event->location;
    if (! isset($rooms_import[trim($event->location)]))
      $rooms_import[trim($event->location)] = trim($event->location);
  }
  $rooms_pb = array_unique($rooms_pb);
  
  $rooms_new = array_diff($rooms_pb, $rooms_db);
  $rooms_deleted = array_diff($rooms_db, $rooms_pb);
  
  return array(
      $rooms_new,
      $rooms_deleted
  );
}

function prepare_events($file) {
  global $rooms_import;
  $data = read_xml($file);
  
  $rooms = sql_select("SELECT * FROM `Room`");
  $rooms_db = array();
  foreach ($rooms as $room)
    $rooms_db[$room['Name']] = $room['RID'];
  
  $events = $data->vcalendar->vevent;
  $shifts_pb = array();
  foreach ($events as $event) {
    $event_pb = $event->children("http://pentabarf.org");
    $event_id = trim($event_pb->{
      'event-id' });
    $shifts_pb[$event_id] = array(
        'start' => DateTime::createFromFormat("Ymd\THis", $event->dtstart)->getTimestamp(),
        'end' => DateTime::createFromFormat("Ymd\THis", $event->dtend)->getTimestamp(),
        'RID' => $rooms_import[trim($event->location)],
        'name' => trim($event->summary),
        'URL' => trim($event->url),
        'PSID' => $event_id 
    );
  }
  
  $shifts = sql_select("SELECT * FROM `Shifts` WHERE `PSID` IS NOT NULL ORDER BY `start`");
  $shifts_db = array();
  foreach ($shifts as $shift)
    $shifts_db[$shift['PSID']] = $shift;
  
  $shifts_new = array();
  $shifts_updated = array();
  foreach ($shifts_pb as $shift)
    if (! isset($shifts_db[$shift['PSID']]))
      $shifts_new[] = $shift;
    else {
      $tmp = $shifts_db[$shift['PSID']];
      if ($shift['name'] != $tmp['name'] || $shift['start'] != $tmp['start'] || $shift['end'] != $tmp['end'] || $shift['RID'] != $tmp['RID'] || $shift['URL'] != $tmp['URL'])
        $shifts_updated[] = $shift;
    }
  
  $shifts_deleted = array();
  foreach ($shifts_db as $shift)
    if (! isset($shifts_pb[$shift['PSID']]))
      $shifts_deleted[] = $shift;
  
  return array(
      $shifts_new,
      $shifts_updated,
      $shifts_deleted 
  );
}

function read_xml($file) {
  global $xml_import;
  if (! isset($xml_import))
    $xml_import = simplexml_load_file($file);
  return $xml_import;
}

function shifts_printable($shifts) {
  global $rooms_import;
  $rooms = array_flip($rooms_import);
  
  uasort($shifts, 'shift_sort');
  
  $shifts_printable = array();
  foreach ($shifts as $shift)
    $shifts_printable[] = array(
        'day' => date("l, Y-m-d", $shift['start']),
        'start' => date("H:i", $shift['start']),
        'name' => shorten($shift['name']),
        'end' => date("H:i", $shift['end']),
        'room' => $rooms[$shift['RID']] 
    );
  return $shifts_printable;
}

function shift_sort($a, $b) {
  return ($a['start'] < $b['start']) ? - 1 : 1;
}
?>
