<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room;

class ShiftsController extends ApiController
{
    public function entriesByRoom(Request $request): Response
    {
        $roomId = (int) $request->getAttribute('room_id');
        /** @var Room $room */
        $room = Room::findOrFail($roomId);
        $shifts = $room->shifts()
            ->with([
                'shiftEntries.angelType',
                'shiftEntries.user.contact',
                'shiftEntries.user.personalData',
                'shiftType',
            ])
            ->get();
        $shiftEntries = [];

        // Blob of not-optimized mediocre pseudo-serialization
        foreach ($shifts as $shift) {
            $entries = [];
            foreach ($shift->shiftEntries as $entry) {
                $user = $entry->user;
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->personalData->first_name,
                    'last_name' => $user->personalData->last_name,
                    'pronoun' => $user->personalData->pronoun,
                    'contact' => $user->contact->only(['dect', 'mobile']),
                    'url' => $this->url->to('/users', ['action' => 'view', 'user_id' => $user->id]),
                ];

                $angelTypeData = $entry->angelType->only(['id', 'name']);
                $angelTypeData['url'] = $this->url->to(
                    '/angeltypes',
                    ['action' => 'view', 'angeltype_id' => $entry->angelType->id]
                );

                $entries[] = [
                    'user' => $userData,
                    'type' => $angelTypeData,
                ];
            }

            $roomData = $room->only(['id', 'name']);
            $roomData['url'] = $this->url->to('/rooms', ['action' => 'view', 'room_id' => $room->id]);

            $shiftEntries[] = [
                'id' => $shift->id,
                'title' => $shift->title,
                'description' => $shift->description,
                'start' => $shift->start,
                'end' => $shift->end,
                'entries' => $entries,
                'room' => $roomData,
                'shift_type' => $shift->shiftType->only(['id', 'name', 'description']),
                'created_at' => $shift->created_at,
                'updated_at' => $shift->updated_at,
                'url' => $this->url->to('/shifts', ['action' => 'view', 'shift_id' => $shift->id]),
            ];
        }

        $data = ['data' => $shiftEntries];
        return $this->response
            ->withContent(json_encode($data));
    }
}
