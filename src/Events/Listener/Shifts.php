<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Carbon\Carbon;
use Engelsystem\Events\DataEvent;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\ModelEvent;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class Shifts
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer,
        protected EventDispatcher $event,
    ) {
    }

    public function deletingShiftsList(DataEvent $event): void
    {
        $shifts = $event->data;
        if (!$shifts instanceof Collection) {
            $shifts = Shift::whereIn('id', $shifts->pluck('id'))->get();
        }
        /** @var Collection|Shift[] $shifts */
        $shifts->load([
            'shiftEntries.shift.shiftType',
            'shiftEntries.shift.location',
            'shiftEntries.user.personalData',
            'shiftEntries.freeloadedBy',
            'shiftEntries.angelType',
        ]);

        foreach ($shifts as $shift) {
            $this->event->dispatch(new ModelEvent('model.shifts.shift.deleting', $shift));
        }
    }

    public function deletingModelWithShifts(ModelEvent $event): void
    {
        /** @var Location|ShiftType $model */
        $model = $event->model;

        $this->event->dispatch(new DataEvent('shifts.deleting', $model->shifts));
    }

    public function deletingModelWithShiftEntries(ModelEvent $event): void
    {
        /** @var Shift|AngelType $model */
        $model = $event->model;
        $entries = $model->shiftEntries;
        $entries->load([
            'shift.shiftType',
            'shift.location',
            'user.personalData',
            'freeloadedBy',
            'angelType',
        ]);

        foreach ($entries as $entry) {
            $this->event->dispatch(new ModelEvent('model.shifts.shiftentry.deleting', $entry));
        }
    }

    public function deletingShiftEntryCreateWorklog(ModelEvent $event): void
    {
        /** @var ShiftEntry $entry */
        $entry = $event->model;
        $shift = $entry->shift;
        if ($entry->freeloaded_by || $shift->start > Carbon::now()) {
            return;
        }

        $worklog = new Worklog();
        $worklog->user()->associate($entry->user);
        $worklog->creator()->associate(auth()->user());
        $worklog->worked_at = $shift->start->copy()->startOfDay();
        $worklog->hours =
            (($shift->end->timestamp - $shift->start->timestamp) / 60 / 60)
            * $shift->getNightShiftMultiplier();
        $worklog->description = Str::substr(sprintf(
            __('%s (%s as %s) in %s, %s - %s'),
            $shift->shiftType->name,
            $shift->title,
            $entry->angelType->name,
            $shift->location->name,
            $shift->start->format(__('general.datetime')),
            $shift->end->format(__('general.datetime'))
        ), 0, 200);
        $worklog->save();

        $this->log->info(
            'Created worklog entry from shift for {user} ({uid}): {worklog})',
            ['user' => $worklog->user->name, 'uid' => $worklog->user->id, 'worklog' => $worklog->description]
        );
    }

    public function deletingShiftEntrySendEmail(ModelEvent $event): void
    {
        /** @var ShiftEntry $entry */
        $entry = $event->model;
        $shift = $entry->shift;

        if (!$entry->user->settings->email_shiftinfo) {
            return;
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

    public function updatingSendEmail(ModelEvent $event): void
    {
        /** @var Shift $shift */
        $shift = $event->model;
        /** @var Shift $oldShift */
        $oldShift = $shift->newModelQuery()->find($shift->id);
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

            if (
                !$user->settings->email_shiftinfo
                || $shift->end < Carbon::now() && $oldShift->end < Carbon::now()
            ) {
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
