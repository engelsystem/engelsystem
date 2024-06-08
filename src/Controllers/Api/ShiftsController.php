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
use Illuminate\Database\Eloquent\Collection;

class ShiftsController extends ApiController
{
    use UsesAuth;

    public function entriesByAngeltype(Request $request): Response
    {
        $id = (int) $request->getAttribute('angeltype_id');
        /** @var AngelType $angeltype */
        $angeltype = AngelType::findOrFail($id);
        /** @var ShiftEntry[]|Collection $shifts */
        $shiftEntries = $angeltype->shiftEntries()
            ->with([
                'shift.neededAngelTypes.angelType',
                'shift.location.neededAngelTypes.angelType',
                'shift.shiftEntries.angelType',
                'shift.shiftEntries.user.contact',
                'shift.shiftEntries.user.personalData',
                'shift.shiftType',
                'shift.schedule.shiftType.neededAngelTypes.angelType',
            ])
            ->get();

        /** @var Shift[]|Collection $shifts */
        $shifts = Collection::make(
            $shiftEntries
                ->pluck('shift')
                ->sortBy('start')
        );

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByLocation(Request $request): Response
    {
        $locationId = (int) $request->getAttribute('location_id');
        /** @var Location $location */
        $location = Location::findOrFail($locationId);
        /** @var Shift[]|Collection $shifts */
        $shifts = $location->shifts()
            ->with([
                'neededAngelTypes.angelType',
                'location.neededAngelTypes.angelType',
                'shiftEntries.angelType',
                'shiftEntries.user.contact',
                'shiftEntries.user.personalData',
                'shiftType',
                'schedule.shiftType.neededAngelTypes.angelType',
            ])
            ->orderBy('start')
            ->get();

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByShiftType(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shifttype_id');
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::findOrFail($shiftTypeId);
        /** @var Shift[]|Collection $shifts */
        $shifts = $shiftType->shifts()
            ->with([
                'neededAngelTypes.angelType',
                'location.neededAngelTypes.angelType',
                'shiftEntries.angelType',
                'shiftEntries.user.contact',
                'shiftEntries.user.personalData',
                'shiftType',
                'schedule.shiftType.neededAngelTypes.angelType',
            ])
            ->orderBy('start')
            ->get();

        return $this->shiftEntriesResponse($shifts);
    }

    public function entriesByUser(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        /** @var ShiftEntry[]|Collection $shifts */
        $shiftEntries = $user->shiftEntries()
            ->with([
                'shift.neededAngelTypes.angelType',
                'shift.location.neededAngelTypes.angelType',
                'shift.shiftEntries.angelType',
                'shift.shiftEntries.user.contact',
                'shift.shiftEntries.user.personalData',
                'shift.shiftType',
                'shift.schedule.shiftType.neededAngelTypes.angelType',
            ])
            ->get();

        /** @var Shift[]|Collection $shifts */
        $shifts = Collection::make(
            $shiftEntries
                ->pluck('shift')
                ->sortBy('start')
        );

        return $this->shiftEntriesResponse($shifts);
    }

    protected function shiftEntriesResponse(Collection $shifts): Response
    {
        /** @var Collection|Shift[] $shifts */
        $shiftEntries = [];
        // Blob of not-optimized mediocre pseudo-serialization
        foreach ($shifts as $shift) {
            // Get all needed/used angel types
            /** @var Collection|NeededAngelType[] $neededAngelTypes */
            $neededAngelTypes = $this->getNeededAngelTypes($shift);

            $angelTypes = new Collection();
            foreach ($neededAngelTypes as $neededAngelType) {
                $entries = $neededAngelType->entries ?: new Collection();

                // Skip empty entries
                if ($neededAngelType->count <= 0 && $entries->isEmpty()) {
                    continue;
                }

                $entries = $entries->map(fn(ShiftEntry $entry) => [
                    'user' => UserResource::toIdentifierArray($entry->user),
                    'freeloaded' => $entry->freeloaded,
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

        // Add needed angel types from additionally added users
        foreach ($shift->shiftEntries as $entry) {
            $neededAngelType = $neededAngelTypes->where('angel_type_id', $entry->angelType->id)->first();
            if (!$neededAngelType) {
                $neededAngelType = new NeededAngelType([
                    'shift_id' => $shift->id,
                    'angel_type_id' => $entry->angelType->id,
                    'count' => 0,
                ]);
                $neededAngelTypes[] = $neededAngelType;
            }

            // Add entries to needed angel type
            $neededAngelType->entries = isset($neededAngelType->entries)
                ? $neededAngelType->entries
                : new Collection();
            $neededAngelType->entries[] = $entry;
        }

        return $neededAngelTypes;
    }
}
