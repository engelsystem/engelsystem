<?php

namespace Engelsystem\Events\Listener;

use Carbon\Carbon;
use Engelsystem\Helpers\Shifts;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Shifts\Shift as ShiftModel;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class Shift
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer
    ) {
    }

    public function shiftDeletingCreateWorklogs(ShiftModel $shift): void
    {
        foreach ($shift->shiftEntries as $entry) {
            if ($entry->freeloaded || $shift->start > Carbon::now()) {
                continue;
            }

            $workLog = new Worklog();
            $workLog->user()->associate($entry->user);
            $workLog->creator()->associate(auth()->user());
            $workLog->worked_at = $shift->start->copy()->startOfDay();
            $workLog->hours =
                (($shift->end->timestamp - $shift->start->timestamp) / 60 / 60)
                * Shifts::getNightShiftMultiplier($shift->start, $shift->end);
            $workLog->comment = sprintf(
                __('%s (%s as %s) in %s, %s - %s'),
                $shift->shiftType->name,
                $shift->title,
                $entry->angelType->name,
                $shift->location->name,
                $shift->start->format(__('general.datetime')),
                $shift->end->format(__('general.datetime'))
            );
            $workLog->save();

            $this->log->info(
                'Created worklog entry from shift for {user} ({uid}): {worklog})',
                ['user' => $workLog->user->name, 'uid' => $workLog->user->id, 'worklog' => $workLog->comment]
            );
        }
    }

    public function shiftDeletingSendEmails(ShiftModel $shift): void
    {
        foreach ($shift->shiftEntries as $entry) {
            if (!$entry->user->settings->email_shiftinfo) {
                continue;
            }

            $this->mailer->sendViewTranslated(
                $entry->user,
                'notification.shift.deleted',
                'emails/worklog-from-shift',
                [
                    'shift' => $shift,
                    'entry' => $entry,
                    'username' => $entry->user->displayName,
                ]
            );
        }
    }

    public function updatedShiftSendEmail(
        ShiftModel $shift,
        ShiftModel $oldShift
    ): void {
        // Only send e-mail on relevant changes
        if (
            $oldShift->shift_type_id == $shift->shift_type_id
            && $oldShift->title == $shift->title
            && $oldShift->start == $shift->start
            && $oldShift->end == $shift->end
            && $oldShift->location_id == $shift->location_id
        ) {
            return;
        }

        $shift->load(['shiftType', 'location']);
        $oldShift->load(['shiftType', 'location']);
        /** @var ShiftEntry[]|Collection $shiftEntries */
        $shiftEntries = $shift->shiftEntries()
            ->with(['angelType', 'user.settings'])
            ->get();

        foreach ($shiftEntries as $shiftEntry) {
            $user = $shiftEntry->user;
            $angelType = $shiftEntry->angelType;

            if (!$user->settings->email_shiftinfo || $shift->end < Carbon::now()) {
                continue;
            }

            $this->mailer->sendViewTranslated(
                $user,
                'notification.shift.updated',
                'emails/updated-shift',
                [
                    'shift' => $shift,
                    'oldShift' => $oldShift,
                    'angelType' => $angelType,
                    'username' => $user->displayName,
                ]
            );
        }
    }
}
