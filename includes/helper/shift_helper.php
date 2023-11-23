<?php

namespace Engelsystem\Events\Listener;

use Carbon\Carbon;
use Engelsystem\Helpers\Shifts;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Location;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
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
}
