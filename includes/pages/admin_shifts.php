<?php

function admin_shifts_title() {
  return _("Create shifts");
}

// Assistent zum Anlegen mehrerer neuer Schichten
function admin_shifts() {
  $msg = "";
  $ok = true;
  
  $rid = 0;
  $start = DateTime::createFromFormat("Y-m-d H:i", date("Y-m-d") . " 00:00")->getTimestamp();
  $end = $start + 24 * 60 * 60;
  $mode = 'single';
  $angelmode = 'location';
  $length = '';
  $change_hours = array();
  $name = "";
  
  // Locations laden (auch unsichtbare - fuer Erzengel ist das ok)
  $rooms = sql_select("SELECT * FROM `Room` ORDER BY `Name`");
  $room_array = array();
  foreach ($rooms as $room)
    $room_array[$room['RID']] = $room['Name'];
    
    // Engeltypen laden
  $types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
  $needed_angel_types = array();
  foreach ($types as $type)
    $needed_angel_types[$type['id']] = 0;
  
  if (isset($_REQUEST['preview']) || isset($_REQUEST['back'])) {
    // Name/Bezeichnung der Schicht, darf leer sein
    $name = strip_request_item('name');
    
    // Auswahl der sichtbaren Locations für die Schichten
    if (isset($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset($room_array[$_REQUEST['rid']]))
      $rid = $_REQUEST['rid'];
    else {
      $ok = false;
      $rid = $rooms[0]['RID'];
      $msg .= error("Wähle bitte einen Raum aus.", true);
    }
    
    if (isset($_REQUEST['start']) && $tmp = DateTime::createFromFormat("Y-m-d H:i", trim($_REQUEST['start'])))
      $start = $tmp->getTimestamp();
    else {
      $ok = false;
      $msg .= error("Bitte gib einen Startzeitpunkt für die Schichten an.", true);
    }
    
    if (isset($_REQUEST['end']) && $tmp = DateTime::createFromFormat("Y-m-d H:i", trim($_REQUEST['end'])))
      $end = $tmp->getTimestamp();
    else {
      $ok = false;
      $msg .= error("Bitte gib einen Endzeitpunkt für die Schichten an.", true);
    }
    
    if ($start >= $end) {
      $ok = false;
      $msg .= error("Das Ende muss nach dem Startzeitpunkt liegen!", true);
    }
    
    if (isset($_REQUEST['mode'])) {
      if ($_REQUEST['mode'] == 'single') {
        $mode = 'single';
      } elseif ($_REQUEST['mode'] == 'multi') {
        if (isset($_REQUEST['length']) && preg_match("/^[0-9]+$/", trim($_REQUEST['length']))) {
          $mode = 'multi';
          $length = trim($_REQUEST['length']);
        } else {
          $ok = false;
          $msg .= error("Bitte gib eine Schichtlänge in Minuten an.", true);
        }
      } elseif ($_REQUEST['mode'] == 'variable') {
        if (isset($_REQUEST['change_hours']) && preg_match("/^([0-9]{2}(,|$))/", trim(str_replace(" ", "", $_REQUEST['change_hours'])))) {
          $mode = 'variable';
          $change_hours = array_map('trim', explode(",", $_REQUEST['change_hours']));
        } else {
          $ok = false;
          $msg .= error("Bitte gib die Schichtwechsel-Stunden kommagetrennt ein.", true);
        }
      }
    } else {
      $ok = false;
      $msg .= error("Bitte wähle einen Modus.", true);
    }
    
    if (isset($_REQUEST['angelmode'])) {
      if ($_REQUEST['angelmode'] == 'location') {
        $angelmode = 'location';
      } elseif ($_REQUEST['angelmode'] == 'manually') {
        $angelmode = 'manually';
        foreach ($types as $type) {
          if (isset($_REQUEST['type_' . $type['id']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['id']]))) {
            $needed_angel_types[$type['id']] = trim($_REQUEST['type_' . $type['id']]);
          } else {
            $ok = false;
            $msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['name'] . ".", true);
          }
        }
        if (array_sum($needed_angel_types) == 0) {
          $ok = false;
          $msg .= error("Es werden 0 Engel benötigt. Bitte wähle benötigte Engel.", true);
        }
      } else {
        $ok = false;
        $msg .= error("Bitte Wähle einen Modus für die benötigten Engel.", true);
      }
    } else {
      $ok = false;
      $msg .= error("Bitte wähle benötigte Engel.", true);
    }
    
    // Beim Zurück-Knopf das Formular zeigen
    if (isset($_REQUEST['back']))
      $ok = false;
      
      // Alle Eingaben in Ordnung
    if ($ok) {
      if ($angelmode == 'location') {
        $needed_angel_types = array();
        $needed_angel_types_location = sql_select("SELECT * FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($rid));
        foreach ($needed_angel_types_location as $type)
          $needed_angel_types[$type['angel_type_id']] = $type['count'];
      }
      $shifts = array();
      if ($mode == 'single') {
        $shifts[] = array(
            'start' => $start,
            'end' => $end,
            'RID' => $rid,
            'name' => $name 
        );
      } elseif ($mode == 'multi') {
        $shift_start = $start;
        do {
          $shift_end = $shift_start + $length * 60;
          
          if ($shift_end > $end)
            $shift_end = $end;
          if ($shift_start >= $shift_end)
            break;
          
          $shifts[] = array(
              'start' => $shift_start,
              'end' => $shift_end,
              'RID' => $rid,
              'name' => $name 
          );
          
          $shift_start = $shift_end;
        } while ($shift_end < $end);
      } elseif ($mode == 'variable') {
        rsort($change_hours);
        $day = DateTime::createFromFormat("Y-m-d H:i", date("Y-m-d", $start) . " 00:00")->getTimestamp();
        $change_index = 0;
        // Ersten/nächsten passenden Schichtwechsel suchen
        foreach ($change_hours as $i => $change_hour) {
          if ($start < $day + $change_hour * 60 * 60)
            $change_index = $i;
          elseif ($start == $day + $change_hour * 60 * 60) {
            // Start trifft Schichtwechsel
            $change_index = ($i + count($change_hours) - 1) % count($change_hours);
            break;
          } else
            break;
        }
        
        $shift_start = $start;
        do {
          $day = DateTime::createFromFormat("Y-m-d H:i", date("Y-m-d", $shift_start) . " 00:00")->getTimestamp();
          $shift_end = $day + $change_hours[$change_index] * 60 * 60;
          
          if ($shift_end > $end)
            $shift_end = $end;
          if ($shift_start >= $shift_end)
            $shift_end += 24 * 60 * 60;
          
          $shifts[] = array(
              'start' => $shift_start,
              'end' => $shift_end,
              'RID' => $rid,
              'name' => $name 
          );
          
          $shift_start = $shift_end;
          $change_index = ($change_index + count($change_hours) - 1) % count($change_hours);
        } while ($shift_end < $end);
      }
      
      $shifts_table = array();
      foreach ($shifts as $shift) {
        $shifts_table_entry = array(
            'timeslot' => '<span class="glyphicon glyphicon-time"></span> ' . date("Y-m-d H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . '<br /><span class="glyphicon glyphicon-map-marker"></span> ' . $room_array[$shift['RID']],
            'entries' => $shift['name'] 
        );
        foreach ($types as $type) {
          if (isset($needed_angel_types[$type['id']]) && $needed_angel_types[$type['id']] > 0)
            $shifts_table_entry['entries'] .= '<br /><span class="icon-icon_angel"></span> <b>' . $type['name'] . ':</b> ' . $needed_angel_types[$type['id']] . ' missing';
        }
        $shifts_table[] = $shifts_table_entry;
      }
      
      // Fürs Anlegen zwischenspeichern:
      $_SESSION['admin_shifts_shifts'] = $shifts;
      $_SESSION['admin_shifts_types'] = $needed_angel_types;
      
      $hidden_types = "";
      foreach ($needed_angel_types as $type_id => $count)
        $hidden_types .= form_hidden('type_' . $type_id, $count);
      return page_with_title(_("Preview"), array(
          form(array(
              $hidden_types,
              form_hidden('name', $name),
              form_hidden('rid', $rid),
              form_hidden('start', date("Y-m-d H:i", $start)),
              form_hidden('end', date("Y-m-d H:i", $end)),
              form_hidden('mode', $mode),
              form_hidden('length', $length),
              form_hidden('change_hours', implode(', ', $change_hours)),
              form_hidden('angelmode', $angelmode),
              form_submit('back', _("back")),
              table(array(
                  'timeslot' => _("Timeslot"),
                  'entries' => _("Entries") 
              ), $shifts_table),
              form_submit('submit', _("Save")) 
          )) 
      ));
    }
  } elseif (isset($_REQUEST['submit'])) {
    if (! is_array($_SESSION['admin_shifts_shifts']) || ! is_array($_SESSION['admin_shifts_types']))
      redirect(page_link_to('admin_shifts'));
    
    foreach ($_SESSION['admin_shifts_shifts'] as $shift) {
      $shift['URL'] = null;
      $shift['PSID'] = null;
      $shift_id = Shift_create($shift);
      if ($shift_id === false)
        engelsystem_error('Unable to create shift.');
      
      engelsystem_log("Shift created: " . $shift['name'] . " from " . date("Y-m-d H:i", $shift['start']) . " to " . date("Y-m-d H:i", $shift['end']));
      $needed_angel_types_info = array();
      foreach ($_SESSION['admin_shifts_types'] as $type_id => $count) {
        $angel_type_source = sql_select("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($type_id) . " LIMIT 1");
        if (count($angel_type_source) > 0) {
          sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`=" . sql_escape($shift_id) . ", `angel_type_id`=" . sql_escape($type_id) . ", `count`=" . sql_escape($count));
          $needed_angel_types_info[] = $angel_type_source[0]['name'] . ": " . $count;
        }
      }
    }
    
    engelsystem_log("Shift needs following angel types: " . join(", ", $needed_angel_types_info));
    $msg = success("Schichten angelegt.", true);
  } else {
    unset($_SESSION['admin_shifts_shifts']);
    unset($_SESSION['admin_shifts_types']);
  }
  
  if (! isset($_REQUEST['rid']))
    $_REQUEST['rid'] = null;
  $room_select = html_select_key('rid', 'rid', $room_array, $_REQUEST['rid']);
  $angel_types = "";
  foreach ($types as $type)
    $angel_types .= form_spinner('type_' . $type['id'], $type['name'], $needed_angel_types[$type['id']]);
  
  return page_with_title(admin_shifts_title(), array(
      msg(),
      $msg,
      form(array(
          form_text('name', _("Name"), $name),
          // TODO: form_textarea('description', _("Description"), ''),
          form_select('rid', _("Room"), $room_array, $_REQUEST['rid']),
          '<div class="row">',
          '<div class="col-md-6">',
          form_text('start', _("Start"), date("Y-m-d H:i", $start)),
          form_text('end', _("End"), date("Y-m-d H:i", $end)),
          form_info(_("Mode"), ''),
          form_radio('mode', _("Create one shift"), $mode == 'single', 'single'),
          form_radio('mode', _("Create multiple shifts"), $mode == 'multi', 'multi'),
          form_text('length', _("Length"), ! empty($_REQUEST['length']) ? $_REQUEST['length'] : '120'),
          form_radio('mode', _("Create multiple shifts with variable length"), $mode == 'variable', 'variable'),
          form_text('change_hours', _("Shift change hours"), ! empty($_REQUEST['change_hours']) ? $_REQUEST['change_hours'] : '00, 04, 08, 10, 12, 14, 16, 18, 20, 22'),
          '</div>',
          '<div class="col-md-6">',
          form_info(_("Needed angels"), ''),
          form_radio('angelmode', _("Take needed angels from room settings"), $angelmode == 'location', 'location'),
          form_radio('angelmode', _("The following angels are needed"), $angelmode == 'manually', 'manually'),
          $angel_types,
          '</div>',
          '</div>',
          form_submit('preview', _("Preview")) 
      )) 
  ));
}
?>
