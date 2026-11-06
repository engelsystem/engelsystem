<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Controllers\Api\Resources\ShiftWithEntriesResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ShiftsController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the web privilege of the page showing the shifts on the website. Shifts of
     * other users are filtered in entriesByUser() like on their profile page.
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return match ($method) {
            'entriesByAngeltype' => 'angeltypes.view',
            'entriesByLocation' => 'locations.view',
            'entriesByShiftType' => 'user_shifts',
            'entriesByUser' => true,
            default => null,
        };
    }

    public function entriesByAngeltype(Request $request): Response
    {
        $id = (int) $request->getAttribute('angeltype_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($id);

        // From users assigned to shifts
        $shiftsByEntries = Shift::query()
            ->join('shift_entries', 'shift_entries.shift_id', 'shifts.id')
            ->where('angel_type_id', $angelType->id)
            ->select('shifts.*');

        // Needed by a shift directly
        $shiftsByShift = Shift::query()
            ->join('needed_angel_types', 'needed_angel_types.shift_id', 'shifts.id')
            ->where('needed_angel_types.angel_type_id', $angelType->id)
            ->select('shifts.*');

        // Needed by connected schedule via shift location
        $shiftsByScheduleLocation = Shift::query()
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', false)
            ->join('needed_angel_types', 'needed_angel_types.location_id', 'shifts.location_id')
            ->where('needed_angel_types.angel_type_id', $angelType->id)
            ->select('shifts.*');

        // Needed by connected schedule via schedule shift type
        $shiftsByScheduleShiftType = Shift::query()
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', true)
            ->join('needed_angel_types', 'needed_angel_types.shift_type_id', 'schedules.shift_type')
            ->where('needed_angel_types.angel_type_id', $angelType->id)
            ->select('shifts.*');

        $shifts = $shiftsByShift
            ->union($shiftsByEntries)
            ->union($shiftsByScheduleLocation)
            ->union($shiftsByScheduleShiftType);

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByLocation(Request $request): Response
    {
        $locationId = (int) $request->getAttribute('location_id');
        /** @var Location $location */
        $location = Location::findOrFail($locationId);

        // Needed by a shift directly
        $shiftsByShift = $location->shifts();

        // Needed by connected schedule via shift location
        $shiftByScheduleLocation = Shift::query()
            ->where('shifts.location_id', $location->id)
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', false)
            ->join('needed_angel_types', 'needed_angel_types.location_id', 'shifts.location_id')
            ->select('shifts.*');

        // Needed by connected schedule via schedule shift type
        $shiftsByScheduleShiftType = Shift::query()
            ->where('shifts.location_id', $location->id)
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', true)
            ->join('needed_angel_types', 'needed_angel_types.shift_type_id', 'schedules.shift_type')
            ->select('shifts.*');

        $shifts = $shiftsByShift
            ->union($shiftByScheduleLocation)
            ->union($shiftsByScheduleShiftType);

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByShiftType(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shifttype_id');
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::findOrFail($shiftTypeId);

        // Needed by a shift directly
        $shiftsByShift = $shiftType->shifts();

        // Needed by connected schedule via shift location
        $shiftsByScheduleLocation = Shift::query()
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', false)
            ->where('schedules.shift_type', $shiftType->id)
            ->join('needed_angel_types', 'needed_angel_types.location_id', 'shifts.location_id')
            ->select('shifts.*');

        // Needed by connected schedule via schedule shift type
        $shiftsByScheduleShiftType = Shift::query()
            ->join('schedule_shift', 'schedule_shift.shift_id', 'shifts.id')
            ->join('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->where('schedules.needed_from_shift_type', true)
            ->where('schedules.shift_type', $shiftType->id)
            ->select('shifts.*');

        $shifts = $shiftsByShift
            ->union($shiftsByScheduleLocation)
            ->union($shiftsByScheduleShiftType);

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByUser(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $shifts = Shift::query()
            ->join('shift_entries', 'shift_entries.shift_id', 'shifts.id')
            ->where('shift_entries.user_id', $user->id)
            ->groupBy('shifts.id')
            ->select('shifts.*');

        // Like on the profile page on the website, other users' shifts are only shown to shift
        // admins and to supporters of the angel types of their entries
        $currentUser = $this->auth?->user();
        if (
            $currentUser?->id != $user->id
            && !$this->hasApiPrivilege()
            && !$this->auth?->can('user_shifts_admin')
        ) {
            $shifts->whereIn('shift_entries.angel_type_id', $this->supportedAngelTypes());
        }

        return $this->shiftEntriesResponse($shifts);
    }

    protected function shiftEntriesResponse(BuilderContract $shifts): Response
    {
        $shifts = $shifts
            ->with([
                'neededAngelTypes.angelType',
                'location.neededAngelTypes.angelType',
                'shiftEntries.angelType',
                'shiftEntries.user.contact',
                'shiftEntries.user.personalData',
                'shiftType',
                'scheduleShift',
                'schedule.shiftType.neededAngelTypes.angelType',
                'tags',
                'createdBy',
                'updatedBy',
            ])
            ->orderBy('start')
            ->get();
        /** @var Shift[]|Collection $shifts */

        $freeloadDetailsVisible = $this->freeloadDetailsVisibility();
        $canSeeEditors = (bool) $this->auth?->can('admin_shifts');

        $shiftEntries = [];
        // Blob of not-optimized mediocre pseudo-serialization
        foreach ($shifts as $shift) {
            // Get all needed/used angel types
            /** @var Collection|NeededAngelType[] $neededAngelTypes */
            $neededAngelTypes = $this->getNeededAngelTypes($shift);

            if ($neededAngelTypes->isEmpty()) {
                continue;
            }

            $angelTypes = new Collection();
            foreach ($neededAngelTypes as $neededAngelType) {
                $entries = $neededAngelType->entries ?: new Collection();

                // Skip empty entries
                if ($neededAngelType->count <= 0 && $entries->isEmpty()) {
                    continue;
                }

                $entries = $entries->map(fn(ShiftEntry $entry) => [
                    'id' => $entry->id,
                    'user' => UserResource::toIdentifierArray($entry->user),
                    'freeloaded' => (bool) $entry->freeloaded_by,
                    'freeloaded_by' => $entry->freeloaded_by && $freeloadDetailsVisible($entry)
                        ? UserResource::toIdentifierArray($entry->freeloadedBy)
                        : null,
                ]);
                $angelTypeData = AngelTypeResource::toIdentifierArray($neededAngelType->angelType);
                $angelTypes[] = new Collection([
                    'angel_type' => $angelTypeData,
                    'needs' => $neededAngelType->count,
                    'entries' => $entries,
                ]);
            }

            $locationData = new LocationResource($shift->location);
            $shiftEntries[] = [
                ...(new ShiftWithEntriesResource($shift))->toArray($locationData, $angelTypes),
                ...$this->editorData($shift, $canSeeEditors),
            ];
        }

        $data = ['data' => $shiftEntries];
        return $this->response
            ->withContent(json_encode($data));
    }

    /**
     * Collect all needed angel types
     */
    /**
     * Who marked an entry as freeloaded is only shown to shift admins and supporters of the
     * entry's angel type on the website; "api" users keep seeing it for all entries
     *
     * @return callable(ShiftEntry): bool
     */
    protected function freeloadDetailsVisibility(): callable
    {
        if ($this->hasApiPrivilege() || $this->auth?->can('user_shifts_admin')) {
            return fn(ShiftEntry $entry): bool => true;
        }

        $supportedAngelTypes = $this->supportedAngelTypes();

        return fn(ShiftEntry $entry): bool => $supportedAngelTypes->contains($entry->angel_type_id);
    }

    /**
     * Ids of the angel types the current user is a supporter of
     */
    protected function supportedAngelTypes(): SupportCollection
    {
        return $this->auth?->user()
            ?->userAngelTypes()
            ->wherePivot('supporter', true)
            ->pluck('angel_types.id')
            ?? new SupportCollection();
    }

    /**
     * Who created and last edited a shift is only shown on the website with "admin_shifts"
     */
    protected function editorData(Shift $shift, bool $canSeeEditors): array
    {
        return [
            'created_by' => $canSeeEditors && $shift->createdBy
                ? UserResource::toIdentifierArray($shift->createdBy)
                : null,
            'updated_by' => $canSeeEditors && $shift->updatedBy
                ? UserResource::toIdentifierArray($shift->updatedBy)
                : null,
        ];
    }

    protected function getNeededAngelTypes(Shift $shift): Collection
    {
        $neededAngelTypes = new Collection();
        if (!$shift->schedule) {
            // Get from shift
            $neededAngelTypes = $shift->neededAngelTypes;
        } elseif ($shift->schedule->needed_from_shift_type) {
            // Load instead from shift type
            $neededAngelTypes = $shift->schedule->shiftType->neededAngelTypes;
        } elseif (!$shift->schedule->needed_from_shift_type) {
            // Load instead from location
            $neededAngelTypes = $shift->location->neededAngelTypes;
        }

        // Create new instances of needed angel types to allow extension with entries per shift
        $neededAngelTypes = $neededAngelTypes->map(function ($value) {
            return clone $value;
        });

        // Add entries and additional angel types from manually added users
        foreach ($shift->shiftEntries as $entry) {
            // Ensure that angel type exists in list; add it if not
            $neededAngelType = $neededAngelTypes->where('angel_type_id', $entry->angelType->id)->first();
            if (!$neededAngelType) {
                $neededAngelType = new NeededAngelType([
                    'shift_id' => $shift->id,
                    'angel_type_id' => $entry->angelType->id,
                    'count' => 0,
                ]);
                $neededAngelTypes[] = $neededAngelType;
            }

            // Initialize entries attribute for manually added users
            if (!isset($neededAngelType->entries)) {
                $neededAngelType->entries = new Collection();
            }

            // Add entries to needed angel type
            $neededAngelType->entries[] = $entry;
        }

        return $neededAngelTypes;
    }
}
