<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\ShiftsController;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;

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
        /** @var Shift $shiftA */
        $shiftA = Shift::factory(1)
            ->create(['room_id' => $room->id, 'start' => Carbon::now()->subHour()])
            ->first();
        /** @var Shift $shiftB */
        $shiftB = Shift::factory(1)
            ->create(['room_id' => $room->id, 'start' => Carbon::now()->addHour()])
            ->first();

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

        // First shift
        $shiftAData = $data['data'][0];
        $this->assertEquals($shiftA->title, $shiftAData['title'], 'Title is equal');
        $this->assertEquals($room->id, $shiftAData['room']['id'], 'Same room');
        $this->assertEquals($shiftA->shiftType->id, $shiftAData['shift_type']['id'], 'Shift type equals');
        $this->assertCount(4, $shiftAData['entries']);
        // Has users
        $entriesA = collect($shiftAData['entries'])->sortBy('type.id');
        $entry = $entriesA[0];
        $this->assertCount(2, $entry['users']);
        $this->assertEquals(5, $entry['needs']);
        $user = $entry['users'][0];
        $this->assertEquals('/users?action=view&user_id=' . $user['id'], $user['url']);
        $this->assertCount(0, $entriesA[1]['users']);
        $this->assertCount(1, $entriesA[2]['users']);
        $this->assertCount(1, $entriesA[3]['users']);

        // Second (empty) shift
        $shiftBData = $data['data'][1];
        $this->assertEquals($shiftB->title, $shiftBData['title'], 'Title is equal');
        $this->assertEquals($room->id, $shiftBData['room']['id'], 'Same room');
        $this->assertEquals($shiftB->shiftType->id, $shiftBData['shift_type']['id'], 'Shift type equals');
        $this->assertCount(2, $shiftBData['entries']);
        // No users
        $entriesB = collect($shiftBData['entries'])->sortBy('type.id');
        $this->assertCount(0, $entriesB[0]['users']);
    }
}