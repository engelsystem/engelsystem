<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Config\Config;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Group;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Question;
use Engelsystem\Models\Session;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Models\UserSupervisorStatus;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\Models\ModelTest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserTest extends ModelTest
{
    use ArraySubsetAsserts;

    /** @var string[] */
    protected array $data = [
        'name'     => 'lorem',
        'password' => '',
        'email'    => 'foo@bar.batz',
        'api_key'  => '',
    ];

    public function hasOneRelationsProvider(): array
    {
        return [
            [
                Contact::class,
                'contact',
                [
                    'dect'   => '1234567',
                    'email'  => 'foo@bar.batz',
                    'mobile' => '1234/12341234',
                ],
            ],
            [
                PersonalData::class,
                'personalData',
                [
                    'first_name' => 'Foo',
                ],
            ],
            [
                Settings::class,
                'settings',
                [
                    'language' => 'de_DE',
                    'theme'    => 4,
                ],
            ],
            [
                State::class,
                'state',
                [
                    'force_active' => true,
                    'force_food' => true,
                ],
            ],
            [
                License::class,
                'license',
                [
                    'has_car'   => true,
                    'drive_car' => true,
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function hasManyRelationsProvider(): array
    {
        return [
            'news' => [
                News::class,
                'news',
                [
                    [
                        'title'      => 'Hey hoo',
                        'text'       => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.',
                        'is_meeting' => false,
                    ],
                    [
                        'title'      => 'Huuhuuu',
                        'text'       => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.',
                        'is_meeting' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function belongsToManyRelationsProvider(): array
    {
        return [
            'groups' => [
                Group::class,
                'groups',
                [
                    [
                        'name' => 'Lorem',
                    ],
                    [
                        'name' => 'Ipsum',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\User\User::contact
     * @covers       \Engelsystem\Models\User\User::license
     * @covers       \Engelsystem\Models\User\User::personalData
     * @covers       \Engelsystem\Models\User\User::settings
     * @covers       \Engelsystem\Models\User\User::state
     *
     * @dataProvider hasOneRelationsProvider
     *
     * @throws Exception
     */
    public function testHasOneRelations(string $class, string $name, array $data): void
    {
        $user = new User($this->data);
        $user->save();

        /** @var HasUserModel $instance */
        $instance = new $class($data);
        $instance->user()
            ->associate($user)
            ->save();

        $this->assertArraySubset($data, (array) $user->{$name}->attributesToArray());
    }

    /**
     * @covers       \Engelsystem\Models\User\User::news()
     *
     * @dataProvider hasManyRelationsProvider
     *
     * @param string $class Class name of the related models
     * @param string $name Name of the accessor for the related models
     * @param array  $modelData List of the related models
     */
    public function testHasManyRelations(string $class, string $name, array $modelData): void
    {
        $user = new User($this->data);
        $user->save();

        $relatedModelIds = [];

        foreach ($modelData as $data) {
            /** @var BaseModel $model */
            $model = $this->app->make($class);
            $stored = $model->create($data + ['user_id' => $user->id]);
            $relatedModelIds[] = $stored->id;
        }

        $this->assertEquals($relatedModelIds, $user->{$name}->modelKeys());
    }


    /**
     * @covers       \Engelsystem\Models\User\User::groups
     *
     * @dataProvider belongsToManyRelationsProvider
     *
     * @param string $class Class name of the related models
     * @param string $name Name of the accessor for the related models
     * @param array  $modelData List of the related models
     */
    public function testBelongsToManyRelations(string $class, string $name, array $modelData): void
    {
        $user = new User($this->data);
        $user->save();

        $relatedModelIds = [];

        foreach ($modelData as $data) {
            /** @var BaseModel $model */
            $model = $this->app->make($class);
            $stored = $model->create($data);
            $stored->users()->attach($user);
            $relatedModelIds[] = $stored->id;
        }

        $this->assertEquals($relatedModelIds, $user->{$name}->modelKeys());
    }

    /**
     * @covers \Engelsystem\Models\User\User::isFreeloader
     */
    public function testIsFreeloader(): void
    {
        $this->app->instance('config', new Config([
            'max_freeloadable_shifts' => 2,
        ]));

        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var User $user3 */
        $user3 = User::factory()->create();

        $userSource = new User($this->data);
        $userSource->save();
        $this->assertFalse($userSource->isFreeloader());

        ShiftEntry::factory()->create(['user_id' => $userSource->id, 'freeloaded_by' => null]);
        ShiftEntry::factory()->create(['user_id' => $userSource->id, 'freeloaded_by' => $user1->id]);
        $this->assertFalse($userSource->isFreeloader());

        ShiftEntry::factory()->create(['user_id' => $userSource->id, 'freeloaded_by' => $user2->id]);
        $this->assertTrue($userSource->isFreeloader());

        ShiftEntry::factory()->create(['user_id' => $userSource->id, 'freeloaded_by' => $user3->id]);
        $this->assertTrue($userSource->isFreeloader());
    }

    /**
     * @covers \Engelsystem\Models\User\User::siftEntriesMarkedFreeloaded
     */
    public function testSiftEntriesMarkedFreeloaded(): void
    {
        /** @var User $freeloader */
        $freeloader = User::factory()->create();

        $user = new User($this->data);
        $user->save();

        ShiftEntry::factory(2)->create(['user_id' => $freeloader->id, 'freeloaded_by' => $user->id]);

        $this->assertCount(2, $user->siftEntriesMarkedFreeloaded);
    }

    /**
     * @covers \Engelsystem\Models\User\User::userAngelTypes
     */
    public function testUserAngelTypes(): void
    {
        AngelType::factory(2)->create();
        $angelType1 = AngelType::factory()->create();
        AngelType::factory(1)->create();
        $angelType2 = AngelType::factory()->create();

        $user = new User($this->data);
        $user->save();

        $user->userAngelTypes()->attach($angelType1);
        $user->userAngelTypes()->attach($angelType2);

        /** @var UserAngelType $userAngelType */
        $userAngelType = UserAngelType::find(1);
        $this->assertEquals($user->id, $userAngelType->user->id);

        $angeltypes = $user->userAngelTypes;
        $this->assertCount(2, $angeltypes);
    }

    /**
     * @covers \Engelsystem\Models\User\User::isAngelTypeSupporter
     */
    public function testIsAngelTypeSupporter(): void
    {
        /** @var AngelType $angelType1 */
        $angelType1 = AngelType::factory()->create();
        /** @var AngelType $angelType2 */
        $angelType2 = AngelType::factory()->create();

        $user = new User($this->data);
        $user->save();

        $user->userAngelTypes()->attach($angelType1, ['supporter' => true]);
        $user->userAngelTypes()->attach($angelType2);

        $this->assertTrue($user->isAngelTypeSupporter($angelType1));
        $this->assertFalse($user->isAngelTypeSupporter($angelType2));
    }

    /**
     * @covers \Engelsystem\Models\User\User::logs
     */
    public function testLogs(): void
    {
        $user = new User($this->data);
        $user->save();

        LogEntry::factory(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->logs);
    }

    /**
     * @covers \Engelsystem\Models\User\User::privileges
     * @covers \Engelsystem\Models\User\User::getPrivilegesAttribute
     */
    public function testPrivileges(): void
    {
        $user = new User($this->data);
        $user->save();

        /** @var Group $group1 */
        $group1 = Group::factory()->create();
        /** @var Group $group2 */
        $group2 = Group::factory()->create();
        /** @var Group $group3 */
        $group3 = Group::factory()->create();
        /** @var Privilege $privilege1 */
        $privilege1 = Privilege::factory()->create();
        /** @var Privilege $privilege2 */
        $privilege2 = Privilege::factory()->create();
        /** @var Privilege $privilege3 */
        $privilege3 = Privilege::factory()->create();
        /** @var Privilege $privilege4 */
        $privilege4 = Privilege::factory()->create();

        $user->groups()->attach($group1);
        $user->groups()->attach($group2);

        $group1->privileges()->attach($privilege1);
        $group1->privileges()->attach($privilege2);

        $group2->privileges()->attach($privilege2);
        $group2->privileges()->attach($privilege3);

        $group3->privileges()->attach($privilege3);
        $group3->privileges()->attach($privilege4);

        /** @var User $createdUser */
        $createdUser = User::first();
        $this->assertInstanceOf(Builder::class, $createdUser->privileges());

        $privileges = $createdUser->privileges->pluck('name');
        $this->assertCount(3, $privileges);
        $this->assertContains($privilege1->name, $privileges);
        $this->assertContains($privilege2->name, $privileges);
        $this->assertContains($privilege3->name, $privileges);
    }

    /**
     * Tests that accessing the NewsComments of an User works.
     *
     * @covers \Engelsystem\Models\User\User::newsComments
     */
    public function testNewsComments(): void
    {
        News::factory()->create();
        ($user = new User($this->data))->save();
        $newsComment = NewsComment::create(['news_id' => 1, 'text' => 'test comment', 'user_id' => $user->id]);
        $comments = $user->newsComments;

        $this->assertCount(1, $comments);
        $comment = $comments->first();
        $this->assertSame($newsComment->id, $comment->id);
    }

    /**
     * Tests that accessing OAuth of an User works
     *
     * @covers \Engelsystem\Models\User\User::oauth
     */
    public function testOauth(): void
    {
        ($user = new User($this->data))->save();
        (new OAuth(['provider' => 'test', 'identifier' => 'LoremIpsumDolor123']))
            ->user()
            ->associate($user)
            ->save();

        $oauth = $user->oauth;

        $this->assertCount(1, $oauth);
    }

    /**
     * @covers \Engelsystem\Models\User\User::shiftEntries
     */
    public function testShiftEntries(): void
    {
        $user = new User($this->data);
        $user->save();

        ShiftEntry::factory(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->shiftEntries);
    }

    /**
     * @covers \Engelsystem\Models\User\User::sessions
     */
    public function testSessions(): void
    {
        $user = new User($this->data);
        $user->save();

        Session::factory(2)->create();
        Session::factory(3)->create(['user_id' => $user->id]);
        Session::factory(2)->create();
        Session::factory(4)->create(['user_id' => $user->id]);

        $this->assertCount(7, $user->sessions);
    }

    /**
     * @covers \Engelsystem\Models\User\User::worklogs
     */
    public function testWorklogs(): void
    {
        ($user = new User($this->data))->save();
        $worklogEntry = Worklog::create([
            'user_id'     => $user->id,
            'creator_id'  => $user->id,
            'hours'       => 1,
            'description' => '',
            'worked_at'   => Carbon::now(),
        ]);

        $worklogs = $user->worklogs;
        $this->assertCount(1, $worklogs);
        $worklog = $worklogs->first();
        $this->assertSame($worklogEntry->id, $worklog->id);
    }

    /**
     * @covers \Engelsystem\Models\User\User::worklogsCreated
     */
    public function testWorklogsCreated(): void
    {
        ($user = new User($this->data))->save();
        $worklogEntry = Worklog::create([
            'user_id'     => $user->id,
            'creator_id'  => $user->id,
            'hours'       => 1,
            'description' => '',
            'worked_at'   => Carbon::now(),
        ]);

        $worklogs = $user->worklogsCreated;
        $this->assertCount(1, $worklogs);
        $worklog = $worklogs->first();
        $this->assertSame($worklogEntry->id, $worklog->id);
    }

    /**
     * @covers \Engelsystem\Models\User\User::questionsAsked
     */
    public function testQuestionsAsked(): void
    {
        ($user = new User($this->data))->save();
        ($user2 = new User(array_merge($this->data, ['name' => 'dolor', 'email' => 'dolor@bar.batz'])))->save();

        ($question1 = new Question(['user_id' => $user->id, 'text' => Str::random()]))->save();
        ($question2 = new Question(['user_id' => $user->id, 'text' => Str::random()]))->save();
        // create some questions asked by user 2 to test the correct assignment
        (new Question(['user_id' => $user2->id, 'text' => Str::random()]))->save();
        (new Question(['user_id' => $user2->id, 'text' => Str::random()]))->save();

        $questionIds = $user->questionsAsked()->pluck('id')->toArray();
        $this->assertCount(2, $questionIds);
        $this->assertContains($question1->id, $questionIds);
        $this->assertContains($question2->id, $questionIds);
    }

    /**
     * @covers \Engelsystem\Models\User\User::questionsAnswered
     */
    public function testQuestionsAnswered(): void
    {
        ($user = new User($this->data))->save();
        ($user2 = new User(array_merge($this->data, ['name' => 'dolor', 'email' => 'dolor@bar.batz'])))->save();

        $questionData = ['user_id' => $user->id, 'text' => Str::random()];
        $answerData = ['answerer_id' => $user2->id, 'answer' => Str::random()];
        ($question1 = new Question(array_merge($questionData, $answerData)))->save();
        ($question2 = new Question(array_merge($questionData, $answerData)))->save();
        // Create some questions asked by user 2 to test the correct assignment
        (new Question(array_merge($questionData, $answerData, ['answerer_id' => $user->id])))->save();
        (new Question($questionData))->save();
        (new Question($questionData))->save();

        $answers = $user2->questionsAnswered()->pluck('id')->toArray();
        $this->assertCount(2, $answers);
        $this->assertContains($question1->id, $answers);
        $this->assertContains($question2->id, $answers);
    }

    /**
     * @covers \Engelsystem\Models\User\User::shiftsCreated
     * @covers \Engelsystem\Models\User\User::shiftsUpdated
     */
    public function testShiftsCreatedAndUpdated(): void
    {
        ($user1 = new User($this->data))->save();
        ($user2 = new User(array_merge($this->data, ['name' => 'foo', 'email' => 'someone@bar.batz'])))->save();

        Shift::factory()->create();
        Shift::factory()->create(['created_by' => $user1->id]);
        Shift::factory()->create(['created_by' => $user2->id, 'updated_by' => $user1->id]);
        Shift::factory()->create(['created_by' => $user1->id, 'updated_by' => $user2->id]);
        Shift::factory()->create(['created_by' => $user2->id, 'updated_by' => $user2->id]);
        Shift::factory()->create(['created_by' => $user1->id]);

        $user1 = User::find(1);
        $this->assertCount(3, $user1->shiftsCreated);
        $this->assertCount(1, $user1->shiftsUpdated);

        $user2 = User::find(2);
        $this->assertCount(2, $user2->shiftsCreated);
        $this->assertCount(2, $user2->shiftsUpdated);
    }

    public function getDisplayNameAttributeProvider(): array
    {
        return [
            ['lorem'],
            ['lorem', ' '],
            ['lorem', null, ' '],
            ['lorem', ' ', ' '],
            ['Test', 'Test', ' '],
            ['Tester', ' ', 'Tester'],
            ['Foo', 'Foo'],
            ['Bar', null, 'Bar'],
            ['Foo Bar', 'Foo', 'Bar'],
            ['Some name', ' Some', ' name'],
            ['Another Surname', ' Another ', ' Surname '],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\User\User::getDisplayNameAttribute
     * @dataProvider getDisplayNameAttributeProvider
     */
    public function testGetDisplayNameAttribute(
        string $expected,
        ?string $firstName = null,
        ?string $lastName = null
    ): void {
        $this->app->instance('config', new Config());

        ($user1 = new User($this->data))->save();
        $user1->personalData->first_name = $firstName;
        $user1->personalData->last_name = $lastName;

        $this->assertEquals('lorem', $user1->displayName);

        config(['display_full_name' => true]);
        $this->assertEquals($expected, $user1->displayName);
    }

    /**
     * @covers \Engelsystem\Models\User\User::minorCategory
     */
    public function testMinorCategory(): void
    {
        $category = MinorCategory::create([
            'name'                    => 'Test Minor Category',
            'allowed_work_categories' => ['A'],
        ]);

        $user = new User($this->data);
        $user->minorCategory()->associate($category);
        $user->save();

        $user = User::find($user->id);
        $this->assertEquals($category->id, $user->minorCategory->id);
        $this->assertEquals('Test Minor Category', $user->minorCategory->name);
    }

    /**
     * @covers \Engelsystem\Models\User\User::consentApprovedBy
     */
    public function testConsentApprovedBy(): void
    {
        $approver = User::factory()->create();

        $user = new User($this->data);
        $user->consentApprovedBy()->associate($approver);
        $user->consent_approved_at = Carbon::now();
        $user->save();

        $user = User::find($user->id);
        $this->assertEquals($approver->id, $user->consentApprovedBy->id);
        $this->assertNotNull($user->consent_approved_at);
    }

    /**
     * @covers \Engelsystem\Models\User\User::guardians
     */
    public function testGuardians(): void
    {
        $user = new User($this->data);
        $user->save();

        /** @var User $guardian1 */
        $guardian1 = User::factory()->create();
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create();

        UserGuardian::create([
            'minor_user_id'    => $user->id,
            'guardian_user_id' => $guardian1->id,
        ]);
        UserGuardian::create([
            'minor_user_id'    => $user->id,
            'guardian_user_id' => $guardian2->id,
        ]);

        $this->assertCount(2, $user->guardians);
    }

    /**
     * @covers \Engelsystem\Models\User\User::minorsGuarded
     */
    public function testMinorsGuarded(): void
    {
        $guardian = new User($this->data);
        $guardian->save();

        /** @var User $minor1 */
        $minor1 = User::factory()->create();
        /** @var User $minor2 */
        $minor2 = User::factory()->create();
        /** @var User $minor3 */
        $minor3 = User::factory()->create();

        UserGuardian::create([
            'minor_user_id'    => $minor1->id,
            'guardian_user_id' => $guardian->id,
        ]);
        UserGuardian::create([
            'minor_user_id'    => $minor2->id,
            'guardian_user_id' => $guardian->id,
        ]);
        UserGuardian::create([
            'minor_user_id'    => $minor3->id,
            'guardian_user_id' => $guardian->id,
        ]);

        $this->assertCount(3, $guardian->minorsGuarded);
    }

    /**
     * @covers \Engelsystem\Models\User\User::supervisorStatus
     */
    public function testSupervisorStatus(): void
    {
        $user = new User($this->data);
        $user->save();

        UserSupervisorStatus::create([
            'user_id'                        => $user->id,
            'willing_to_supervise'           => true,
            'supervision_training_completed' => true,
        ]);

        $user = User::find($user->id);
        $this->assertTrue($user->supervisorStatus->willing_to_supervise);
        $this->assertTrue($user->supervisorStatus->supervision_training_completed);
    }

    /**
     * @covers \Engelsystem\Models\User\User::supervisorStatus
     */
    public function testSupervisorStatusDefault(): void
    {
        $user = new User($this->data);
        $user->save();

        // Access without creating record - should return default
        $this->assertFalse($user->supervisorStatus->willing_to_supervise);
        $this->assertFalse($user->supervisorStatus->supervision_training_completed);
    }

    /**
     * @covers \Engelsystem\Models\User\User::isMinor
     */
    public function testIsMinor(): void
    {
        $category = MinorCategory::create([
            'name'                    => 'Test',
            'allowed_work_categories' => ['A'],
        ]);

        $user1 = new User($this->data);
        $user1->save();
        $this->assertFalse($user1->isMinor());

        $user2 = new User(array_merge($this->data, [
            'name'              => 'minor_user',
            'email'             => 'minor@test.com',
            'minor_category_id' => $category->id,
        ]));
        $user2->save();
        $this->assertTrue($user2->isMinor());
    }

    /**
     * @covers \Engelsystem\Models\User\User::hasConsentApproved
     */
    public function testHasConsentApproved(): void
    {
        $approver = User::factory()->create();

        $user1 = new User($this->data);
        $user1->save();
        $this->assertFalse($user1->hasConsentApproved());

        $user2 = new User(array_merge($this->data, [
            'name'                        => 'consent_user',
            'email'                       => 'consent@test.com',
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]));
        $user2->save();
        $this->assertTrue($user2->hasConsentApproved());
    }
}
