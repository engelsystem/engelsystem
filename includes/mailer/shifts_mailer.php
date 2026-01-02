<?php

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\User;

function mail_shift_assign(User $user, Shift $shift)
{
    if (!$user->settings->email_shiftinfo) {
        return;
    }

    if (auth()->user()?->id == $user->id) {
        return;
    }

    /** @var EngelsystemMailer $mailer */
    $mailer = app('mailer');
    $mailer->sendViewTranslated(
        $user,
        'Assigned to Shift',
        'emails/shift-assigned',
        ['shift' => $shift, 'username' => $user->displayName]
    );
}

function mail_shift_removed(User $user, Shift $shift)
{
    if (!$user->settings->email_shiftinfo) {
        return;
    }

    if (auth()->user()?->id == $user->id) {
        return;
    }

    /** @var EngelsystemMailer $mailer */
    $mailer = app('mailer');
    $mailer->sendViewTranslated(
        $user,
        'Removed from Shift',
        'emails/shift-removed',
        ['shift' => $shift, 'username' => $user->displayName]
    );
}
