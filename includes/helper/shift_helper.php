<?php

namespace Engelsystem\Events\Listener;

use Carbon\Carbon;
use Engelsystem\Helpers\Shifts;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Shift as ShiftModel;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
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

    public function deletedEntryCreateWorklog(
        User $user,
        Carbon $start,
        Carbon $end,
        string $name,
        string $title,
        string $type,
        Location $location,
        bool $freeloaded
    ): void {
        if ($freeloaded || $start > Carbon::now()) {
            return;
        }

        $workLog = new Worklog();
        $workLog->user()->associate($user);
        $workLog->creator()->associate(auth()->user());
        $workLog->worked_at = $start->copy()->startOfDay();
        $workLog->hours =
            (($end->timestamp - $start->timestamp) / 60 / 60)
            * Shifts::getNightShiftMultiplier($start, $end);
        $workLog->comment = sprintf(
            __('%s (%s as %s) in %s, %s - %s'),
            $name,
            $title,
            $type,
            $location->name,
            $start->format(__('general.datetime')),
            $end->format(__('general.datetime'))
        );
        $workLog->save();

        $this->log->info(
            'Created worklog entry from shift for {user} ({uid}): {worklog})',
            ['user' => $workLog->user->name, 'uid' => $workLog->user->id, 'worklog' => $workLog->comment]
        );
    }

    public function deletedEntrySendEmail(
        User $user,
        Carbon $start,
        Carbon $end,
        string $name,
        string $title,
        string $type,
        Location $location,
        bool $freeloaded
    ): void {
        if (!$user->settings->email_shiftinfo) {
            return;
        }

        $this->mailer->sendViewTranslated(
            $user,
            'notification.shift.deleted',
            'emails/worklog-from-shift',
            [
                'name'       => $name,
                'title'      => $title,
                'start'      => $start,
                'end'        => $end,
                'location'   => $location,
                'freeloaded' => $freeloaded,
                'username'   => $user->displayName,
            ]
        );
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
