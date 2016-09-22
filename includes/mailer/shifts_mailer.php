<?php

function mail_shift_change($old_shift, $new_shift) {
  $users = ShiftEntries_by_shift($old_shift["SID"]);
  $old_room = Room($old_shift["RID"]);
  $new_room = Room($new_shift["RID"]);

  $noticable_changes = false;

  $message = _("A Shift you are registered on has changed:");
  $message .= "\r\n";

  if ($old_shift["name"] != $new_shift["name"]) {
    $message .= sprintf(_("* Shift type changed from %s to %s"), $old_shift["name"], $new_shift["name"]) . "\r\n";
    $noticable_changes = true;
  }

  if ($old_shift["title"] != $new_shift["title"]) {
    $message .= sprintf(_("* Shift title changed from %s to %s"), $old_shift["title"], $new_shift["title"]) . "\r\n";
    $noticable_changes = true;
  }

  if ($old_shift["start"] != $new_shift["start"]) {
    $message .= sprintf(_("* Shift Start changed from %s to %s"), date("Y-m-d H:i", $old_shift["start"]), date("Y-m-d H:i", $new_shift["start"])) . "\r\n";
    $noticable_changes = true;
  }

  if ($old_shift["end"] != $new_shift["end"]) {
    $message .= sprintf(_("* Shift End changed from %s to %s"), date("Y-m-d H:i", $old_shift["end"]), date("Y-m-d H:i", $new_shift["end"])) . "\r\n";
    $noticable_changes = true;
  }

  if ($old_shift["RID"] != $new_shift["RID"]) {
    $message .= sprintf(_("* Shift Location changed from %s to %s"), $old_room["Name"], $new_room["Name"]) . "\r\n";
    $noticable_changes = true;
  }

  if (! $noticable_changes) {
    // There are no changes worth sending an E-Mail
    return;
  }

  $message .= "\r\n";
  $message .= _("The updated Shift:") . "\r\n";

  $message .= $new_shift["name"] . "\r\n";
  $message .= $new_shift["title"] . "\r\n";
  $message .= date("Y-m-d H:i", $new_shift["start"]) . " - " . date("H:i", $new_shift["end"]) . "\r\n";
  $message .= $new_room["Name"] . "\r\n";

  foreach ($users as $user)
    if ($user["email_shiftinfo"])
      engelsystem_email_to_user($user, '[engelsystem] ' . _("Your Shift has changed"), $message, true);
}

function mail_shift_delete($shift) {
  $users = ShiftEntries_by_shift($shift["SID"]);
  $room = Room($shift["RID"]);

  $message = _("A Shift you are registered on was deleted:") . "\r\n";

  $message .= $shift["name"] . "\r\n";
  $message .= $shift["title"] . "\r\n";
  $message .= date("Y-m-d H:i", $shift["start"]) . " - " . date("H:i", $shift["end"]) . "\r\n";
  $message .= $room["Name"] . "\r\n";

  foreach ($users as $user)
    if ($user["email_shiftinfo"])
      engelsystem_email_to_user($user, '[engelsystem] ' . _("Your Shift was deleted"), $message, true);
}

function mail_shift_assign($user, $shift) {
  if ($user["email_shiftinfo"]) {
    $room = Room($shift["RID"]);

    $message = _("You have been assigned to a Shift:") . "\r\n";
    $message .= $shift["name"] . "\r\n";
    $message .= $shift["title"] . "\r\n";
    $message .= date("Y-m-d H:i", $shift["start"]) . " - " . date("H:i", $shift["end"]) . "\r\n";
    $message .= $room["Name"] . "\r\n";

    engelsystem_email_to_user($user, '[engelsystem] ' . _("Assigned to Shift"), $message, true);
  }
}

function mail_shift_removed($user, $shift) {
  if ($user["email_shiftinfo"]) {
    $room = Room($shift["RID"]);

    $message = _("You have been removed from a Shift:") . "\r\n";
    $message .= $shift["name"] . "\r\n";
    $message .= $shift["title"] . "\r\n";
    $message .= date("Y-m-d H:i", $shift["start"]) . " - " . date("H:i", $shift["end"]) . "\r\n";
    $message .= $room["Name"] . "\r\n";

    engelsystem_email_to_user($user, '[engelsystem] ' . _("Removed from Shift"), $message, true);
  }
}

?>
