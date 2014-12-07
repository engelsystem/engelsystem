<?php

function mail_shift_change($old_shift, $new_shift) {

  $users = ShiftEntries_by_shift($old_shift["SID"]);
  $old_room = Room($old_shift["RID"]);
  $new_room = Room($new_shift["RID"]);

  foreach ($users as $user) {
    if ($user["email_shiftinfo"]) {

      $noticable_changes = false;

      $message = _("A Shift you are registered on has changed:");
      $message .="\n";

      if($old_shift["name"] != $new_shift["name"]) {
        $message .= sprintf(_("* Shift Name changed from %s to %s"), $old_shift["name"], $new_shift["name"]) . "\n";
        $noticable_changes = true;
      }

      if($old_shift["start"] != $new_shift["start"]) {
        $message .= sprintf(_("* Shift Start changed from %s to %s"),date("y-m-d H:i", $old_shift["start"]), date("y-m-d H:i", $new_shift["start"])) . "\n";
        $noticable_changes = true;
      }

      if($old_shift["end"] != $new_shift["end"]) {
        $message .= sprintf(_("* Shift End changed from %s to %s"),date("y-m-d H:i", $old_shift["end"]), date("y-m-d H:i", $new_shift["end"])) . "\n";
        $noticable_changes = true;
      }

      if($old_shift["RID"] != $new_shift["RID"]) {
        $message .= sprintf(_("* Shift Location changed from %s to %s"), $old_room["Name"], $new_room["Name"]) . "\n";
        $noticable_changes = true;
      }

      if (!noticable_changes) {
        //There are no changes worth sending an E-Mail
        return;
      }

      $message .= "\n";
      $message .= _("The updated Shift:") . "\n";

      $message .= $new_shift["name"] . "\n";
      $message .= date("y-m-d H:i", $new_shift["start"]) . " - " . date("y-m-d H:i", $new_shift["end"]) . "\n";
      $message .= $new_room["Name"] . "\n";

      engelsystem_email_to_user($user, _("[Angelsystem] Your Shift has changed"), $message);
    }
  }
}

?>
