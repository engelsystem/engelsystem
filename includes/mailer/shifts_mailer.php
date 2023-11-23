<?php

use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

function mail_shift_change(Shift $old_shift, Shift $new_shift)
{
    /** @var ShiftEntry[]|Collection $shiftEntries */
    $shiftEntries = $old_shift->shiftEntries()
        ->with(['user', 'user.settings'])
        ->get();
    $old_location = $old_shift->location;
    $new_location = $new_shift->location;

    $noticeable_changes = false;

    $message = __('A Shift you are registered on has changed:');
    $message .= "\n";

    if ($old_shift->shift_type_id != $new_shift->shift_type_id) {
        $message .= sprintf(
            __('* Shift type changed from %s to %s'),
            $old_shift->shiftType->name,
            $new_shift->shiftType->name
        ) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift->title != $new_shift->title) {
        $message .= sprintf(__('* Shift title changed from %s to %s'), $old_shift->title, $new_shift->title) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift->start->timestamp != $new_shift->start->timestamp) {
        $message .= sprintf(
            __('* Shift Start changed from %s to %s'),
            $old_shift->start->format(__('general.datetime')),
            $new_shift->start->format(__('general.datetime'))
        ) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift->end->timestamp != $new_shift->end->timestamp) {
        $message .= sprintf(
            __('* Shift End changed from %s to %s'),
            $old_shift->end->format(__('general.datetime')),
            $new_shift->end->format(__('general.datetime'))
        ) . "\n";
        $noticeable_changes = true;
    }

    if ($old_shift->location_id != $new_shift->location_id) {
        $message .= sprintf(__('* Shift Location changed from %s to %s'), $old_location->name, $new_location->name) . "\n";
        $noticeable_changes = true;
    }

    if (!$noticeable_changes) {
        // There are no changes worth sending an E-Mail
        return;
    }

    $message .= "\n";
    $message .= __('The updated Shift:') . "\n";

    $message .= $new_shift->shiftType->name . "\n";
    $message .= $new_shift->title . "\n";
    $message .= $new_shift->start->format(__('general.datetime')) . ' - ' . $new_shift->end->format(__('H:i')) . "\n";
    $message .= $new_location->name . "\n\n";
    $message .= url('/shifts', ['action' => 'view', 'shift_id' => $new_shift->id]) . "\n";

    foreach ($shiftEntries as $shiftEntry) {
        $user = $shiftEntry->user;
        if ($user->settings->email_shiftinfo) {
            engelsystem_email_to_user(
                $user,
                __('Your Shift has changed'),
                $message,
                true
            );
        }
    }
}

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
