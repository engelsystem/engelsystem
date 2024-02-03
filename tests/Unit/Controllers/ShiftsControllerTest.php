<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\NotificationType;
use Engelsystem\Controllers\ShiftsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;

class ShiftsControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;
    protected Redirector|MockObject $redirect;
    protected UrlGeneratorInterface $url;
    protected User $user;

    /**
     * @covers \Engelsystem\Controllers\ShiftsController::random
     * @covers \Engelsystem\Controllers\ShiftsController::__construct
     */
    public function testRandomNonShiftsFound(): void
    {
        $this->createModels();

        $this->setExpects($this->redirect, 'to', ['http://localhost/shifts'], $this->response);
        $this->setExpects($this->auth, 'user', null, $this->user);

        $controller = new ShiftsController($this->auth, $this->redirect, $this->url);

        $return = $controller->random();
        $this->assertEquals($this->response, $return);
        $this->assertHasNotification('notification.shift.no_next_found', NotificationType::WARNING);
    }

    /**
     * @covers \Engelsystem\Controllers\ShiftsController::random
     * @covers \Engelsystem\Controllers\ShiftsController::getNextFreeShifts
     * @covers \Engelsystem\Controllers\ShiftsController::queryShiftEntries
     */
    public function testRandom(): void
    {
        $this->createModels();
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());
        $start = Carbon::now()->addHour();

        $otherUser = User::factory()->create();
        [$userAngelType, $otherAngelType] = AngelType::factory(2)->create();
        [$possibleShift1, $possibleShift2, $otherAngelTypeShift, $alreadySubscribedShift] = Shift::factory(4)
            ->create(['start' => $start, 'end' => $start->addHours(2)]);
        $this->user->userAngelTypes()->attach($userAngelType, ['confirm_user_id' => $this->user->id]);
        NeededAngelType::factory()->create([
            'shift_id' => $possibleShift1->id,
            'angel_type_id' => $userAngelType->id,
            'count' => 2,
        ]);
        NeededAngelType::factory()->create([
            'shift_id' => $possibleShift2->id,
            'angel_type_id' => $userAngelType->id,
            'count' => 1,
        ]);
        NeededAngelType::factory()->create([
            'shift_id' => $otherAngelTypeShift->id,
            'angel_type_id' => $otherAngelType->id,
            'count' => 3,
        ]);
        ShiftEntry::factory()->create([
            'shift_id' => $alreadySubscribedShift->id,
            'angel_type_id' => $userAngelType->id,
            'user_id' => $this->user->id,
        ]);

        $otherUser->userAngelTypes()->attach($userAngelType, ['confirm_user_id' => $otherUser->id]);
        ShiftEntry::factory()->create([
            'shift_id' => $possibleShift1->id,
            'angel_type_id' => $userAngelType->id,
            'user_id' => $otherUser,
        ]);

        $this->redirect->expects($this->exactly(10))
            ->method('to')
            ->willReturnCallback(function (string $url) use ($possibleShift1, $possibleShift2) {
                parse_str(parse_url($url)['query'] ?? '', $parameters);
                $this->assertTrue(Str::startsWith($url, 'http://localhost/shifts'));
                $this->assertArrayHasKey('shift_id', $parameters);
                $shiftId = $parameters['shift_id'] ?? 0;
                $this->assertTrue(in_array($shiftId, [$possibleShift1->id, $possibleShift2->id]));
                return $this->response;
            });

        $controller = new ShiftsController($this->auth, $this->redirect, $this->url);

        $return = $controller->random();
        $this->assertEquals($this->response, $return);

        // Try multiple times
        for ($i = 1; $i < 10; $i++) {
            $controller->random();
        }
    }

    protected function createModels(): void
    {
        $this->user = User::factory()->create();

        $this->auth = $this->createMock(Authenticator::class);

        $this->redirect = $this->createMock(Redirector::class);

        $this->url = $this->app->make(UrlGeneratorInterface::class);
    }
}
