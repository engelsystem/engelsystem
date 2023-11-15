<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Controllers\Api\Resources\ShiftWithEntriesResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;

class ShiftsController extends ApiController
{
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
            ])
            ->orderBy('start')
            ->get();

        $shiftEntries = [];
        // Blob of not-optimized mediocre pseudo-serialization
        foreach ($shifts as $shift) {
            // Get all needed/used angel types
            $neededAngelTypes = $this->getNeededAngelTypes($shift);

            $entries = new Collection();
            foreach ($neededAngelTypes as $neededAngelType) {
                $users = UserResource::collection($neededAngelType->users ?? []);

                // Skip empty entries
                if ($neededAngelType->count <= 0 && $users->isEmpty()) {
                    continue;
                }

                $angelTypeData = new AngelTypeResource($neededAngelType->angelType);
                $entries[] = new Collection([
                    'users' => $users,
                    'type' => $angelTypeData,
                    'needs' => $neededAngelType->count,
                ]);
            }

            $locationData = new LocationResource($location);
            $shiftEntries[] = (new ShiftWithEntriesResource($shift))->toArray($locationData, $entries);
        }

        $data = ['data' => $shiftEntries];
        return $this->response
            ->withContent(json_encode($data));
    }

    /**
     * Collect all needed angeltypes
     */
    protected function getNeededAngelTypes(Shift $shift): Collection
    {
        // From shift
        $neededAngelTypes = $shift->neededAngelTypes;

        // Add from location
        foreach ($shift->location->neededAngelTypes as $neededAngelType) {
            /** @var NeededAngelType $existingNeededAngelType */
            $existingNeededAngelType = $neededAngelTypes
                ->where('angel_type_id', $neededAngelType->angel_type_id)
                ->first();
            if (!$existingNeededAngelType) {
                $neededAngelTypes[] = clone $neededAngelType;
                continue;
            }

            $existingNeededAngelType->location_id = $shift->location->id;
            $existingNeededAngelType->count += $neededAngelType->count;
        }

        // Add needed angeltypes from additionally added users
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

            // Add users to entries
            $neededAngelType->users = isset($neededAngelType->users)
                ? $neededAngelType->users
                : new Collection();
            $neededAngelType->users[] = $entry->user;
        }

        return $neededAngelTypes;
    }
}
