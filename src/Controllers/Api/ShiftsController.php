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

class ShiftsController extends ApiController
{
    use UsesAuth;

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
            ])
            ->orderBy('start')
            ->get();
        /** @var Shift[]|Collection $shifts */

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
                    'user' => UserResource::toIdentifierArray($entry->user),
                    'freeloaded_by' => $entry->freeloaded_by
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
            $shiftEntries[] = (new ShiftWithEntriesResource($shift))->toArray($locationData, $angelTypes);
        }

        $data = ['data' => $shiftEntries];
        return $this->response
            ->withContent(json_encode($data));
    }

    /**
     * Collect all needed angel types
     */
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
