<?php

namespace Engelsystem\Events\Listener;

use Carbon\Carbon;
use Engelsystem\Helpers\Shifts;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;

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
        Room $room,
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
            $room->name,
            $start->format(__('Y-m-d H:i')),
            $end->format(__('Y-m-d H:i'))
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
        Room $room,
        bool $freeloaded
    ): void {
        if (!$user->settings->email_shiftinfo) {
            return;
        }

        $subject = 'notification.shift.deleted';
        try {
            $this->mailer->sendViewTranslated(
                $user,
                $subject,
                'emails/worklog-from-shift',
                [
                    'name'       => $name,
                    'title'      => $title,
                    'start'      => $start,
                    'end'        => $end,
                    'room'       => $room,
                    'freeloaded' => $freeloaded,
                    'username'   => $user->displayName,
                ]
            );
        } catch (TransportException $e) {
            $this->log->error(
                'Unable to send email "{title}" to user {user} with {exception}',
                ['title' => $subject, 'user' => $user->name, 'exception' => $e]
            );
        }
    }
}
