<?php

declare(strict_types=1);

namespace Engelsystem\Test\Feature\Helpers;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Feature\ApplicationFeatureTest;

class GoodieTest extends ApplicationFeatureTest
{
    /** @var BaseModel[] */
    protected array $createdModels = [];

    /**
     * @covers \Engelsystem\Helpers\Goodie::userScore
     * @covers \Engelsystem\Helpers\Goodie::shiftScoreQuery
     * @covers \Engelsystem\Helpers\Goodie::worklogScoreQuery
     */
    public function testUserScoreNightShift(): void
    {
        $user = new User(['name' => 'gn8', 'email' => 'g@n.8', 'password' => '', 'api_key' => '']);
        $user->save();
        $this->createdModels[] = $user;
        $workLog = new Worklog([
            'user_id' => $user->id,
            'hours' => 3.87,
            'creator_id' => $user->id,
            'description' => '',
            'worked_at' => Carbon::now()->subHour(),
        ]);
        $workLog->save();
        $this->createdModels[] = $workLog;
        $shiftType = new ShiftType([
            'name' => 'Type',
            'description' => '',
        ]);
        $shiftType->save();
        $this->createdModels[] = $shiftType;
        $location = new Location([
            'name' => 'Local',
        ]);
        $location->save();
        $this->createdModels[] = $location;
        $shift = new Shift([
            'title' => 'Shift',
            'start' => Carbon::create('2020-03-02 1:00'),
            'end' => Carbon::create('2020-03-02 4:00'),
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'created_by' => $user->id,
        ]);
        $shift->save();
        $this->createdModels[] = $shift;
        $angelType = new AngelType([
            'name' => 'AngelType',
        ]);
        $angelType->save();
        $this->createdModels[] = $angelType;
        $shiftEntry = new ShiftEntry([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'angel_type_id' => $angelType->id,
        ]);
        $shiftEntry->save();
        $this->createdModels[] = $shiftEntry;

        $result = Goodie::userScore($user);

        $this->assertEquals(9.87, round($result, 2));
    }

    private function deleteModels(): void
    {
        foreach ($this->createdModels as $model) {
            $model->delete();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createdModels = [];
        config([
            'night_shifts' => [
                'enabled' => true,
                'start' => 2,
                'end' => 6,
                'multiplier' => 2,
            ],
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteModels();
    }
}
