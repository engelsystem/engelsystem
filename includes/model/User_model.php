<?php

use Carbon\Carbon;
use Engelsystem\Models\User\User;

/**
 * User model
 */

/**
 * @param User $user
 * @return float
 */
function User_get_eligable_voucher_count($user)
{
    $voucher_settings = config('voucher_settings');
    $start = $voucher_settings['voucher_start']
        ? Carbon::createFromFormat('Y-m-d', $voucher_settings['voucher_start'])->setTime(0, 0)
        : null;

    $shiftEntries = ShiftEntries_finished_by_user($user, $start);
    $worklog = $user->worklogs()
        ->whereDate('worked_at', '>=', $start ?: 0)
        ->with(['user', 'creator'])
        ->get();
    $shifts_done =
        count($shiftEntries)
        + $worklog->count();

    $shiftsTime = 0;
    foreach ($shiftEntries as $shiftEntry) {
        $shiftsTime += ($shiftEntry->shift->end->timestamp - $shiftEntry->shift->start->timestamp) / 60 / 60;
    }
    foreach ($worklog as $entry) {
        $shiftsTime += $entry->hours;
    }

    $vouchers = $voucher_settings['initial_vouchers'];
    if ($voucher_settings['shifts_per_voucher']) {
        $vouchers += $shifts_done / $voucher_settings['shifts_per_voucher'];
    }
    if ($voucher_settings['hours_per_voucher']) {
        $vouchers += $shiftsTime / $voucher_settings['hours_per_voucher'];
    }

    $vouchers -= $user->state->got_voucher;
    $vouchers = floor($vouchers);
    if ($vouchers < 0) {
        return 0;
    }

    return $vouchers;
}
