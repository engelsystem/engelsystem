<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class ShiftsController extends BaseController
{
    use HasUserNotifications;

    /** @var string[] */
    protected array $permissions = [
        'user_shifts',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Redirector $redirect,
        protected UrlGeneratorInterface $url,
    ) {
    }

    public function random(): Response
    {
        $user = $this->auth->user();
        $nextFreeShifts = $this->getNextFreeShifts($user);

        if ($nextFreeShifts->isEmpty()) {
            $this->addNotification('notification.shift.no_next_found', NotificationType::WARNING);
            return $this->redirect->to($this->url->to('/shifts'));
        }

        /** @var Shift $randomShift */
        $randomShift = $nextFreeShifts
            ->collect()
            // Prefer soon starting shifts
            ->groupBy('start')
            // Get first starting shifts
            ->first()
            // Select one of them at random
            ->random();

        return $this->redirect->to($this->url->to('/shifts', ['action' => 'view', 'shift_id' => $randomShift->id]));
    }

    protected function getNextFreeShifts(User $user): Collection | DbCollection
    {
        $angelTypes = $user
            ->userAngelTypes()
            ->select('angel_types.id')
            ->whereNested(function (Builder $query): void {
                $query
                    ->where('angel_types.restricted', false)
                    ->orWhereNot('confirm_user_id', false);
            })
            ->pluck('id');
        /** @var ShiftEntry[]|DbCollection $shiftEntries */
        $shiftEntries = $user->shiftEntries()->with('shift')->get();

        $freeShifts = Shift::query()
            ->select('shifts.*')
            // Load needed from shift if no schedule configured, else from room
            ->leftJoin('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->leftJoin('schedules', 'schedules.id', 'schedule_shift.schedule_id')

            // From shift
            ->leftJoin('needed_angel_types', function (JoinClause $query): void {
                $query->on('needed_angel_types.shift_id', 'shifts.id')
                    ->whereNull('schedule_shift.shift_id');
            })
            // Via schedule shift type
            ->leftJoin('needed_angel_types AS nast', function (JoinClause $query): void {
                $query->on('nast.shift_type_id', 'shifts.shift_type_id')
                    ->whereNotNull('schedule_shift.shift_id')
                    ->where('schedules.needed_from_shift_type', true);
            })
            // Via schedule location
            ->leftJoin('needed_angel_types AS nas', function (JoinClause $query): void {
                $query->on('nas.location_id', 'shifts.location_id')
                    ->whereNotNull('schedule_shift.shift_id')
                    ->where('schedules.needed_from_shift_type', false);
            })

            // Not already signed in
            ->whereNotIn('shifts.id', $shiftEntries->pluck('shift_id'))
            // Same angel types
            ->where(function (EloquentBuilder $query) use ($angelTypes): void {
                $query
                    ->whereIn('needed_angel_types.angel_type_id', $angelTypes)
                    ->orWhereIn('nast.angel_type_id', $angelTypes)
                    ->orWhereIn('nas.angel_type_id', $angelTypes);
            })
            // Starts soon
            ->where('shifts.start', '>', Carbon::now())
            // Where help needed
            ->where(function (Builder $query): void {
                $query
                    ->from('shift_entries')
                    ->selectRaw('COUNT(*)')
                    ->where(fn(Builder $query) => $this->queryShiftEntries($query));
            }, '<', Shift::query()->raw('COALESCE(needed_angel_types.count, nast.count, nas.count)'))
            ->limit(10)
            ->orderBy('start');

        foreach ($shiftEntries as $entry) {
            $freeShifts->where(function (QueryBuilder $query) use ($entry): void {
                $query->where('end', '<=', $entry->shift->start);
                $query->orWhere('start', '>=', $entry->shift->end);
            });
        }

        return $freeShifts->get();
    }

    protected function queryShiftEntries(Builder $query): void
    {
        $query->select('id')
            ->from('shift_entries')
            ->where('shift_entries.shift_id', $query->raw('shifts.id'))
            ->where(function (Builder $query): void {
                $query->where('shift_entries.angel_type_id', $query->raw('needed_angel_types.angel_type_id'))
                ->orWhere('shift_entries.angel_type_id', $query->raw('nas.angel_type_id'))
                ->orWhere('shift_entries.angel_type_id', $query->raw('nast.angel_type_id'));
            })
            ->groupBy(['shift_entries.shift_id', 'shift_entries.angel_type_id']);
    }
}
