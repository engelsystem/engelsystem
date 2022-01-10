<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Models\Faq;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\Question;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Model;

class FactoriesTest extends TestCase
{
    use HasDatabase;

    /** @var string[] */
    protected $models = [
        User::class,
        Contact::class,
        PersonalData::class,
        Settings::class,
        State::class,
        PasswordReset::class,
        Worklog::class,
        News::class,
        NewsComment::class,
        Message::class,
        Faq::class,
        Question::class,
        Room::class,
        Schedule::class,
    ];

    /**
     * Test all existing model factories
     */
    public function testFactories()
    {
        $this->initDatabase();

        foreach ($this->models as $model) {
            $instance = (new $model())->factory()->create();
            $this->assertInstanceOf(Model::class, $instance);
        }
    }
}
