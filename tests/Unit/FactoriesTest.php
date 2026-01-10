<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Group;
use Engelsystem\Models\Location;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Question;
use Engelsystem\Models\Session;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Tag;
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
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
class FactoriesTest extends TestCase
{
    use HasDatabase;

    /**
     * @return string[][]
     */
    public static function factoriesProvider(): array
    {
        return [
            [AngelType::class],
            [Contact::class],
            [Faq::class],
            [Group::class],
            [License::class],
            [Location::class],
            [LogEntry::class],
            [Message::class],
            [NeededAngelType::class],
            [News::class],
            [NewsComment::class],
            [OAuth::class],
            [PasswordReset::class],
            [PersonalData::class],
            [Privilege::class],
            [Question::class],
            [Schedule::class],
            [Session::class],
            [Settings::class],
            [Shift::class],
            [ShiftEntry::class],
            [ShiftType::class],
            [State::class],
            [Tag::class],
            [UserAngelType::class],
            [User::class],
            [Worklog::class],
        ];
    }

    /**
     * Test all model factories
     *
     *
     */
    #[DataProvider('factoriesProvider')]
    public function testFactories(string $model): void
    {
        $this->initDatabase();

        $instance = (new $model())->factory()->create();
        $this->assertInstanceOf(Model::class, $instance);
    }

    public function testStateFactoryArrived(): void
    {
        $this->initDatabase();

        /** @var State $instance */
        $instance = (new State())->factory()->arrived()->create();
        $this->assertInstanceOf(Model::class, $instance);
        $this->assertTrue($instance->arrived);
    }
}
