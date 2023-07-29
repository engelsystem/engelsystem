<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\ShiftsController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

class ShiftsControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByRoom
     * @covers \Engelsystem\Controllers\Api\ShiftsController::getNeededAngelTypes
     */
    public function testEntriesByRoom(): void
    {
        $this->initDatabase();

        /** @var Room $room */
        $room = Room::factory()->create();

        // Shifts
        /** @var Collection|Shift[] $shifts */
        $shifts = Shift::factory(2)
            ->create(['room_id' => $room->id]);
        $shiftA = $shifts[0];

        // "Empty" entry to be skipped
        NeededAngelType::factory(1)->create(['room_id' => null, 'shift_id' => $shiftA->id, 'count' => 0]);

        // Needed entry by shift
        /** @var NeededAngelType $byShift */
        $byShift = NeededAngelType::factory(2)
            ->create(['room_id' => null, 'shift_id' => $shiftA->id, 'count' => 2])
            ->first();

        // Needed entry by room
        /** @var NeededAngelType $byRoom */
        $byRoom = NeededAngelType::factory(1)
            ->create(['room_id' => $room->id, 'shift_id' => null, 'count' => 3])
            ->first();

        // Added by both
        NeededAngelType::factory(1)
            ->create([
                'room_id' => $room->id, 'shift_id' => null, 'angel_type_id' => $byShift->angel_type_id, 'count' => 3,
            ])
            ->first();

        // By shift
        ShiftEntry::factory(2)->create(['shift_id' => $shiftA->id, 'angel_type_id' => $byShift->angel_type_id]);

        // By room
        ShiftEntry::factory(1)->create(['shift_id' => $shiftA->id, 'angel_type_id' => $byRoom->angel_type_id]);

        // Additional (not required by shift nor room)
        ShiftEntry::factory(1)->create(['shift_id' => $shiftA->id]);

        foreach (User::all() as $user) {
            // Generate user data
            /** @var User $user */
            PersonalData::factory()->create(['user_id' => $user->id]);
            Contact::factory()->create(['user_id' => $user->id]);
        }

        $request = new Request();
        $request = $request->withAttribute('room_id', $room->id);

        $controller = new ShiftsController(new Response(), $this->url);

        $response = $controller->entriesByRoom($request);
        $this->validateApiResponse('/rooms/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }
}
