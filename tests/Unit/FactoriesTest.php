<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Group;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Question;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Model;

class FactoriesTest extends TestCase
{
    use HasDatabase;

    /** @var string[] */
    protected array $models = [
        AngelType::class,
        Contact::class,
        Faq::class,
        Group::class,
        License::class,
        Message::class,
        News::class,
        NewsComment::class,
        PasswordReset::class,
        PersonalData::class,
        Privilege::class,
        Question::class,
        Room::class,
        Schedule::class,
        Settings::class,
        ShiftType::class,
        State::class,
        User::class,
        UserAngelType::class,
        Worklog::class,
    ];

    /**
     * Test all existing model factories
     *
     * @covers \Database\Factories\Engelsystem\Models\User\ContactFactory
     * @covers \Database\Factories\Engelsystem\Models\FaqFactory
     * @covers \Database\Factories\Engelsystem\Models\User\LicenseFactory
     * @covers \Database\Factories\Engelsystem\Models\MessageFactory
     * @covers \Database\Factories\Engelsystem\Models\NewsFactory
     * @covers \Database\Factories\Engelsystem\Models\NewsCommentFactory
     * @covers \Database\Factories\Engelsystem\Models\User\PasswordResetFactory
     * @covers \Database\Factories\Engelsystem\Models\User\PersonalDataFactory
     * @covers \Database\Factories\Engelsystem\Models\QuestionFactory
     * @covers \Database\Factories\Engelsystem\Models\RoomFactory
     * @covers \Database\Factories\Engelsystem\Models\Shifts\ScheduleFactory
     * @covers \Database\Factories\Engelsystem\Models\User\SettingsFactory
     * @covers \Database\Factories\Engelsystem\Models\User\StateFactory
     * @covers \Database\Factories\Engelsystem\Models\User\UserFactory
     * @covers \Database\Factories\Engelsystem\Models\WorklogFactory
     */
    public function testFactories(): void
    {
        $this->initDatabase();

        foreach ($this->models as $model) {
            $instance = (new $model())->factory()->create();
            $this->assertInstanceOf(Model::class, $instance);
        }
    }

    /**
     * @covers \Database\Factories\Engelsystem\Models\User\StateFactory
     */
    public function testStateFactoryArrived(): void
    {
        $this->initDatabase();

        /** @var State $instance */
        $instance = (new State())->factory()->arrived()->create();
        $this->assertInstanceOf(Model::class, $instance);
        $this->assertTrue($instance->arrived);
    }
}
