<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\Carbon;
use Engelsystem\Models\User\User;

class UserVouchers
{
    public static function eligibleVoucherCount(User $user): int
    {
        $voucherSettings = config('voucher_settings');
        $start = $voucherSettings['voucher_start']
            ? Carbon::createFromFormat('Y-m-d', $voucherSettings['voucher_start'])->setTime(0, 0)
            : null;

        $shiftEntries = $user->shiftEntries()
            ->join('shifts', 'shift_entries.shift_id', '=', 'shifts.id')
            ->whereDate('shifts.end', '<', Carbon::now())
            ->whereDate('shifts.start', '>=', $start ?: 0)
            ->whereNull('freeloaded_by')
            ->get();
        $worklogs = $user->worklogs()
            ->whereDate('worked_at', '>=', $start ?: 0)
            ->whereDate('worked_at', '<=', Carbon::now())
            ->get();
        $shiftsCount =
            $shiftEntries->count()
            + $worklogs->count();

        $shiftsTime = 0;
        foreach ($shiftEntries as $shiftEntry) {
            $shiftsTime += $shiftEntry->shift->start->diffInHours($shiftEntry->shift->end);
        }
        foreach ($worklogs as $worklog) {
            $shiftsTime += $worklog->hours;
        }

        $vouchers = $voucherSettings['initial_vouchers'];
        if ($voucherSettings['shifts_per_voucher']) {
            $vouchers += $shiftsCount / $voucherSettings['shifts_per_voucher'];
        }
        if ($voucherSettings['hours_per_voucher']) {
            $vouchers += $shiftsTime / $voucherSettings['hours_per_voucher'];
        }

        $vouchers -= $user->state->got_voucher;
        $vouchers = floor($vouchers);
        if ($vouchers <= 0) {
            return 0;
        }

        return (int) $vouchers;
    }
}
