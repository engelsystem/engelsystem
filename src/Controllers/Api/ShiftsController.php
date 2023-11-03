<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

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
                $users = [];
                foreach ($neededAngelType->users ?? [] as $user) {
                    $users[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->personalData->first_name,
                        'last_name' => $user->personalData->last_name,
                        'pronoun' => $user->personalData->pronoun,
                        'contact' => $user->contact->only(['dect', 'mobile']),
                        'url' => $this->url->to('/users', ['action' => 'view', 'user_id' => $user->id]),
                    ];
                }

                // Skip empty entries
                if ($neededAngelType->count <= 0 && empty($users)) {
                    continue;
                }

                $angelTypeData = $neededAngelType->angelType->only(['id', 'name', 'description']);
                $angelTypeData['url'] = $this->url->to(
                    '/angeltypes',
                    ['action' => 'view', 'angeltype_id' => $neededAngelType->angelType->id]
                );

                $entries[] = [
                    'users' => $users,
                    'type' => $angelTypeData,
                    'needs' => $neededAngelType->count,
                ];
            }

            $locationData = $location->only(['id', 'name']);
            $locationData['url'] = $this->url->to('/locations', ['action' => 'view', 'location_id' => $location->id]);

            $shiftEntries[] = [
                'id' => $shift->id,
                'title' => $shift->title,
                'description' => $shift->description,
                'starts_at' => $shift->start,
                'ends_at' => $shift->end,
                'location' => $locationData,
                'shift_type' => $shift->shiftType->only(['id', 'name', 'description']),
                'created_at' => $shift->created_at,
                'updated_at' => $shift->updated_at,
                'entries' => $entries,
                'url' => $this->url->to('/shifts', ['action' => 'view', 'shift_id' => $shift->id]),
            ];
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
