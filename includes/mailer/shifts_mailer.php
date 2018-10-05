<?php

/**
 * @param array $old_shift
 * @param array $new_shift
 */
function mail_shift_change($old_shift, $new_shift)
{
    $users = ShiftEntries_by_shift($old_shift['SID']);
    $old_room = Room($old_shift['RID']);
    $new_room = Room($new_shift['RID']);

    $noticeable_changes = false;

    $message = __('A Shift you are registered on has changed:');
    $message .= "\n";

    if ($old_shift['name'] != $new_shift['name']) {
        $message .= sprintf(__('* Shift type changed from %s to %s'), $old_shift['name'], $new_shift['name']) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift['title'] != $new_shift['title']) {
        $message .= sprintf(__('* Shift title changed from %s to %s'), $old_shift['title'], $new_shift['title']) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift['start'] != $new_shift['start']) {
        $message .= sprintf(
                __('* Shift Start changed from %s to %s'),
                date('Y-m-d H:i', $old_shift['start']),
                date('Y-m-d H:i', $new_shift['start'])
            ) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift['end'] != $new_shift['end']) {
        $message .= sprintf(
                __('* Shift End changed from %s to %s'),
                date('Y-m-d H:i', $old_shift['end']),
                date('Y-m-d H:i', $new_shift['end'])
            ) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift['RID'] != $new_shift['RID']) {
        $message .= sprintf(__('* Shift Location changed from %s to %s'), $old_room['Name'], $new_room['Name']) . "\n";
        $noticeable_changes = true;
    }

    if (!$noticeable_changes) {
        // There are no changes worth sending an E-Mail
        return;
    }

    $message .= "\n";
    $message .= __('The updated Shift:') . "\n";

    $message .= $new_shift['name'] . "\n";
    $message .= $new_shift['title'] . "\n";
    $message .= date('Y-m-d H:i', $new_shift['start']) . ' - ' . date('H:i', $new_shift['end']) . "\n";
    $message .= $new_room['Name'] . "\n";

    foreach ($users as $user) {
        if ($user['email_shiftinfo']) {
            engelsystem_email_to_user(
                $user,
                __('Your Shift has changed'),
                $message,
                true
            );
        }
    }
}

/**
 * @param array $shift
 */
function mail_shift_delete($shift)
{
    $users = ShiftEntries_by_shift($shift['SID']);
    $room = Room($shift['RID']);

    $message = __('A Shift you are registered on was deleted:') . "\n";

    $message .= $shift['name'] . "\n";
    $message .= $shift['title'] . "\n";
    $message .= date('Y-m-d H:i', $shift['start']) . ' - ' . date('H:i', $shift['end']) . "\n";
    $message .= $room['Name'] . "\n";

    foreach ($users as $user) {
        if ($user['email_shiftinfo']) {
            engelsystem_email_to_user($user, __('Your Shift was deleted'), $message, true);
        }
    }
}

/**
 * @param array $user
 * @param array $shift
 */
function mail_shift_assign($user, $shift)
{
    if (!$user['email_shiftinfo']) {
        return;
    }

    $room = Room($shift['RID']);

    $message = __('You have been assigned to a Shift:') . "\n";
    $message .= $shift['name'] . "\n";
    $message .= $shift['title'] . "\n";
    $message .= date('Y-m-d H:i', $shift['start']) . ' - ' . date('H:i', $shift['end']) . "\n";
    $message .= $room['Name'] . "\n";

    engelsystem_email_to_user($user, __('Assigned to Shift'), $message, true);
}

/**
 * @param array $user
 * @param array $shift
 */
function mail_shift_removed($user, $shift)
{
    if (!$user['email_shiftinfo']) {
        return;
    }

    $room = Room($shift['RID']);

    $message = __('You have been removed from a Shift:') . "\n";
    $message .= $shift['name'] . "\n";
    $message .= $shift['title'] . "\n";
    $message .= date('Y-m-d H:i', $shift['start']) . ' - ' . date('H:i', $shift['end']) . "\n";
    $message .= $room['Name'] . "\n";

    engelsystem_email_to_user($user, __('Removed from Shift'), $message, true);
}
