<?php

use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\User;

function mail_shift_assign(User $user, Shift $shift)
{
    if (!$user->settings->email_shiftinfo) {
        return;
    }

    $message = __('You have been assigned to a Shift:') . "\n";
    $message .= $shift->shiftType->name . "\n";
    $message .= $shift->title . "\n";
    $message .= $shift->start->format(__('general.datetime')) . ' - ' . $shift->end->format(__('H:i')) . "\n";
    $message .= $shift->location->name . "\n\n";
    $message .= url('/shifts', ['action' => 'view', 'shift_id' => $shift->id]) . "\n";

    engelsystem_email_to_user($user, __('Assigned to Shift'), $message, true);
}

function mail_shift_removed(User $user, Shift $shift)
{
    if (!$user->settings->email_shiftinfo) {
        return;
    }

    $message = __('You have been removed from a Shift:') . "\n";
    $message .= $shift->shiftType->name . "\n";
    $message .= $shift->title . "\n";
    $message .= $shift->start->format(__('general.datetime')) . ' - ' . $shift->end->format(__('H:i')) . "\n";
    $message .= $shift->location->name . "\n";

    engelsystem_email_to_user($user, __('Removed from Shift'), $message, true);
}
