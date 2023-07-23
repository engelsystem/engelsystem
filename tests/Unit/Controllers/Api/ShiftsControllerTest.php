<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\ShiftsController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;

class ShiftsControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByRoom
     */
    public function testEntriesByRoom(): void
    {
        $this->initDatabase();

        /** @var Room $room */
        $room = Room::factory()->create();

        Shift::factory(3)
            ->has(ShiftEntry::factory(2), 'shiftEntries')
            ->create(['room_id' => $room->id]);

        foreach (User::all() as $user) {
            // Generate user data
            /** @var User $user */
            PersonalData::factory()->create(['user_id' => $user->id]);
            Contact::factory()->create(['user_id' => $user->id]);
        }

        $request = new Request();
        $request = $request->withAttribute('room_id', $room->id);

        $controller = new ShiftsController(new Response());

        $response = $controller->entriesByRoom($request);
        $this->validateApiResponse('/rooms', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
    }
}
