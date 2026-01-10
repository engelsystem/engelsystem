<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use Carbon\Carbon;
use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Location;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Question;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LogLevel;

#[CoversMethod(Stats::class, 'vouchers')]
#[CoversMethod(Stats::class, 'vouchersQuery')]
#[CoversMethod(Stats::class, '__construct')]
#[CoversMethod(Stats::class, 'vouchersBuckets')]
#[CoversMethod(Stats::class, 'goodies')]
#[CoversMethod(Stats::class, 'tshirtSizes')]
#[CoversMethod(Stats::class, 'raw')]
#[CoversMethod(Stats::class, 'languages')]
#[CoversMethod(Stats::class, 'themes')]
#[CoversMethod(Stats::class, 'licenses')]
#[CoversMethod(Stats::class, 'worklogSeconds')]
#[CoversMethod(Stats::class, 'worklogBuckets')]
#[CoversMethod(Stats::class, 'getBuckets')]
#[CoversMethod(Stats::class, 'locations')]
#[CoversMethod(Stats::class, 'angelTypes')]
#[CoversMethod(Stats::class, 'angelTypesSum')]
#[CoversMethod(Stats::class, 'shiftTypes')]
#[CoversMethod(Stats::class, 'shifts')]
#[CoversMethod(Stats::class, 'announcements')]
#[CoversMethod(Stats::class, 'comments')]
#[CoversMethod(Stats::class, 'questions')]
#[CoversMethod(Stats::class, 'usersState')]
#[CoversMethod(Stats::class, 'usersInfo')]
#[CoversMethod(Stats::class, 'forceActiveUsers')]
#[CoversMethod(Stats::class, 'forceFoodUsers')]
#[CoversMethod(Stats::class, 'usersPronouns')]
#[CoversMethod(Stats::class, 'email')]
#[CoversMethod(Stats::class, 'currentlyWorkingUsers')]
#[CoversMethod(Stats::class, 'faq')]
#[CoversMethod(Stats::class, 'messages')]
#[CoversMethod(Stats::class, 'sessions')]
#[CoversMethod(Stats::class, 'getQuery')]
#[CoversMethod(Stats::class, 'oauth')]
#[CoversMethod(Stats::class, 'databaseRead')]
#[CoversMethod(Stats::class, 'databaseWrite')]
#[CoversMethod(Stats::class, 'logEntries')]
#[CoversMethod(Stats::class, 'passwordResets')]
class StatsTest extends TestCase
{
    use HasDatabase;

    public function testVouchers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(14, $stats->vouchers());
    }

    public function testVouchersBuckets(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals([1 => 6, 3 => 8, '+Inf' => 9], $stats->vouchersBuckets([1, 3, '+Inf']));
    }

    public function testGoodies(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->goodies());
    }

    public function testTshirtSizes(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $sizes = $stats->tshirtSizes();
        $this->assertCount(2, $sizes);
        $this->assertEquals([
            ['shirt_size' => 'L', 'count' => 2],
            ['shirt_size' => 'XXL', 'count' => 1],
        ], $sizes->toArray());
    }

    public function testLanguages(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $languages = $stats->languages();
        $this->assertCount(2, $languages);
        $this->assertEquals([
            ['language' => 'lo_RM', 'count' => 2],
            ['language' => 'te_ST', 'count' => 7],
        ], $languages->toArray());
    }

    public function testThemes(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $themes = $stats->themes();
        $this->assertCount(3, $themes);
        $this->assertEquals([
            ['theme' => 0, 'count' => 6],
            ['theme' => 1, 'count' => 2],
            ['theme' => 4, 'count' => 1],
        ], $themes->toArray());
    }

    public function testLicenses(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(1, $stats->licenses('has_car'));
        $this->assertEquals(1, $stats->licenses('forklift'));
        $this->assertEquals(1, $stats->licenses('car'));
        $this->assertEquals(0, $stats->licenses('3.5t'));
        $this->assertEquals(0, $stats->licenses('7.5t'));
        $this->assertEquals(0, $stats->licenses('12t'));
        $this->assertEquals(1, $stats->licenses('ifsg_light'));
        $this->assertEquals(0, $stats->licenses('ifsg'));
        $this->assertEquals(0, $stats->licenses('forklift', true));
        $this->assertEquals(1, $stats->licenses('car', true));
        $this->assertEquals(0, $stats->licenses('3.5t', true));
        $this->assertEquals(0, $stats->licenses('7.5t', true));
        $this->assertEquals(1, $stats->licenses('12t', true));
        $this->assertEquals(0, $stats->licenses('ifsg_light', true));
        $this->assertEquals(1, $stats->licenses('ifsg', true));
    }

    public function testWorklogSeconds(): void
    {
        $this->addUsers();
        $worklogData = [
            'user_id'     => 1,
            'creator_id'  => 1,
            'hours'       => 2.4,
            'description' => '',
            'worked_at'   => new Carbon(),
        ];
        (new Worklog($worklogData))->save();
        (new Worklog(['hours' => 1.2, 'user_id' => 3] + $worklogData))->save();

        $stats = new Stats($this->database);
        $seconds = $stats->worklogSeconds();

        $this->assertEquals(2.4 * 60 * 60 + 1.2 * 60 * 60, $seconds);
    }

    public function testWorklogBuckets(): void
    {
        Worklog::factory()->create(['hours' => 1.2, 'worked_at' => Carbon::now()->subDay()]);
        Worklog::factory()->create(['hours' => 1.9, 'worked_at' => Carbon::now()->subDay()]);
        Worklog::factory()->create(['hours' => 3, 'worked_at' => Carbon::now()->subDay()]);
        Worklog::factory()->create(['hours' => 10, 'worked_at' => Carbon::now()->subDay()]);

        $stats = new Stats($this->database);
        $buckets = $stats->worklogBuckets([
            1 * 60 * 60,
            2 * 60 * 60,
            3 * 60 * 60,
            4 * 60 * 60,
            '+Inf',
        ]);

        $this->assertEquals([
            3600   => 0,
            7200   => 2,
            10800  => 3,
            14400  => 3,
            '+Inf' => 4,
        ], $buckets);
    }

    public function testLocations(): void
    {
        (new Location(['name' => 'Location 1']))->save();
        (new Location(['name' => 'Second location']))->save();
        (new Location(['name' => 'Another location']))->save();
        (new Location(['name' => 'Old location']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->locations());
    }

    public function testAngelTypes(): void
    {
        (new AngelType(['id' => 1, 'name' => 'AngelType 1', 'restricted' => true]))->save();
        (new AngelType(['id' => 2, 'name' => 'Second AngelType', 'restricted' => false]))->save();
        (new AngelType(['id' => 3, 'name' => 'Another AngelType', 'restricted' => true]))->save();
        (new AngelType(['id' => 4, 'name' => 'Old AngelType', 'restricted' => false]))->save();
        UserAngelType::factory()->create(['angel_type_id' => 1, 'confirm_user_id' => 1, 'supporter' => true]);
        UserAngelType::factory()->create(['angel_type_id' => 1, 'confirm_user_id' => null, 'supporter' => false]);
        UserAngelType::factory()->create(['angel_type_id' => 1, 'confirm_user_id' => 1, 'supporter' => false]);
        UserAngelType::factory()->create(['angel_type_id' => 2, 'confirm_user_id' => null, 'supporter' => true]);
        UserAngelType::factory()->create(['angel_type_id' => 2, 'confirm_user_id' => null, 'supporter' => false]);
        UserAngelType::factory()->create(['angel_type_id' => 2, 'confirm_user_id' => null, 'supporter' => false]);

        $stats = new Stats($this->database);
        $this->assertEquals([
            [
                'name' => 'AngelType 1',
                'restricted' => true,
                'supporters' => 1,
                'confirmed' => 1,
                'unconfirmed' => 1,
            ],
            [
                'name' => 'Another AngelType',
                'restricted' => true,
                'unconfirmed' => 0,
                'supporters' => 0,
                'confirmed' => 0,
            ],
            [
                'name' => 'Old AngelType',
                'restricted' => false,
                'unconfirmed' => 0,
                'supporters' => 0,
                'confirmed' => 0,
            ],
            [
                'name' => 'Second AngelType',
                'restricted' => false,
                'unconfirmed' => 0,
                'supporters' => 1,
                'confirmed' => 2,
            ],
            ], $stats->angelTypes());
    }

    public function testAngelTypesSum(): void
    {
        (new AngelType(['name' => 'AngelType 1']))->save();
        (new AngelType(['name' => 'Second AngelType']))->save();
        (new AngelType(['name' => 'Another AngelType']))->save();
        (new AngelType(['name' => 'Old AngelType']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->angelTypesSum());
    }

    public function testShiftTypes(): void
    {
        (new ShiftType(['name' => 'ShiftType 1', 'description' => 'rtfm']))->save();
        (new ShiftType(['name' => 'Second ShiftType', 'description' => 'pebkac']))->save();
        (new ShiftType(['name' => 'Another ShiftType', 'description' => 'id10t error']))->save();
        (new ShiftType(['name' => 'Old ShiftType', 'description' => 'layer 8']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->shiftTypes());
    }

    public function testShifts(): void
    {
        Shift::factory(5)->create();

        $stats = new Stats($this->database);
        $this->assertEquals(5, $stats->shifts());
    }

    public function testAnnouncements(): void
    {
        $this->addUsers();
        $newsData = ['title' => 'Test', 'text' => 'Foo Bar', 'user_id' => 1];

        (new News($newsData))->save();
        (new News($newsData))->save();
        (new News($newsData + ['is_meeting' => true]))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->announcements());
        $this->assertEquals(2, $stats->announcements(false));
        $this->assertEquals(1, $stats->announcements(true));
    }

    public function testComments(): void
    {
        $user = $this->addUser();

        $news = new News(['title' => 'Test', 'text' => 'Foo Bar', 'user_id' => $user->id]);
        $news->save();

        foreach (['Test', 'Another text!'] as $text) {
            $comment = new NewsComment(['text' => $text]);
            $comment->news()->associate($news);
            $comment->user()->associate($user);
            $comment->save();
        }

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->comments());
    }

    public function testQuestions(): void
    {
        $this->addUsers();
        $questionsData = ['text' => 'Lorem Ipsum', 'user_id' => 1];

        (new Question($questionsData))->save();
        (new Question($questionsData))->save();
        (new Question($questionsData + ['answerer_id' => 2, 'answer' => 'Dolor sit!']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->questions());
        $this->assertEquals(2, $stats->questions(false));
        $this->assertEquals(1, $stats->questions(true));
    }

    public function testUsersState(): void
    {
        $this->addUsers();
        ShiftEntry::factory()->create(['user_id' => 3]);
        ShiftEntry::factory()->create(['user_id' => 4]);
        ShiftEntry::factory()->create(['user_id' => 1]);

        $stats = new Stats($this->database);
        $this->assertEquals(7, $stats->usersState());
        $this->assertEquals(5, $stats->usersState(false));
        $this->assertEquals(2, $stats->usersState(true));
        $this->assertEquals(2, $stats->usersState(null, false));
        $this->assertEquals(1, $stats->usersState(true, false));
        $this->assertEquals(1, $stats->usersState(false, false));
    }

    public function testUsersInfo(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(1, $stats->usersInfo());
    }

    public function testForceActiveUsers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->forceActiveUsers());
    }

    public function testForceFoodUsers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->forceFoodUsers());
    }

    public function testUsersPronouns(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->usersPronouns());
    }

    public function testEmail(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(0, $stats->email('not-available-option'));
        $this->assertEquals(2, $stats->email('system'));
        $this->assertEquals(3, $stats->email('humans'));
        $this->assertEquals(1, $stats->email('goodie'));
        $this->assertEquals(1, $stats->email('news'));
    }

    public function testCurrentlyWorkingUsers(): void
    {
        $this->addUsers();
        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create(['start' => Carbon::now()->subHour(), 'end' => Carbon::now()->addHour()]);

        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'freeloaded_by' => null]);
        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'freeloaded_by' => null]);
        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'freeloaded_by' => $user1->id]);

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->currentlyWorkingUsers());
        $this->assertEquals(2, $stats->currentlyWorkingUsers(false));
        $this->assertEquals(1, $stats->currentlyWorkingUsers(true));
    }

    public function testFaq(): void
    {
        (new Faq(['question' => 'Foo?', 'text' => 'Bar!']))->save();
        (new Faq(['question' => 'Lorem??', 'text' => 'Ipsum!!!']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->faq());
    }

    public function testMessages(): void
    {
        $this->addUsers();

        (new Message(['user_id' => 1, 'receiver_id' => 2, 'text' => 'Ohi?']))->save();
        (new Message(['user_id' => 4, 'receiver_id' => 1, 'text' => 'Testing stuff?']))->save();
        (new Message(['user_id' => 2, 'receiver_id' => 3, 'text' => 'Nope!', 'read' => true]))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->messages());
    }

    public function testSessions(): void
    {
        $this->database
            ->getConnection()
            ->table('sessions')
            ->insert([
                ['id' => 'asd', 'payload' => 'data', 'last_activity' => new Carbon('1 month ago')],
                ['id' => 'efg', 'payload' => 'lorem', 'last_activity' => new Carbon('55 minutes ago')],
                ['id' => 'hij', 'payload' => 'ipsum', 'last_activity' => new Carbon('3 seconds ago')],
                ['id' => 'klm', 'payload' => 'dolor', 'last_activity' => new Carbon()],
            ]);

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->sessions());
    }

    public function testOauth(): void
    {
        $this->addUsers();
        $user1 = User::find(1);
        $user2 = User::find(2);
        $user3 = User::find(3);

        (new OAuth(['provider' => 'test', 'identifier' => '1']))->user()->associate($user1)->save();
        (new OAuth(['provider' => 'test', 'identifier' => '2']))->user()->associate($user2)->save();
        (new OAuth(['provider' => 'another-provider', 'identifier' => 'usr3']))->user()->associate($user3)->save();

        $stats = new Stats($this->database);
        $oauth = $stats->oauth();

        $this->assertCount(2, $oauth);
        $this->assertEquals([
            ['provider' => 'another-provider', 'count' => 1],
            ['provider' => 'test', 'count' => 2],
        ], $oauth->toArray());
    }

    public function testDatabase(): void
    {
        $stats = new Stats($this->database);

        $read = $stats->databaseRead();
        $write = $stats->databaseWrite();

        $this->assertIsFloat($read);
        $this->assertNotEmpty($read);
        $this->assertIsFloat($write);
        $this->assertNotEmpty($write);
    }

    public function testLogEntries(): void
    {
        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Some info']))->save();
        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Another info']))->save();
        (new LogEntry(['level' => LogLevel::CRITICAL, 'message' => 'A critical error!']))->save();
        (new LogEntry(['level' => LogLevel::DEBUG, 'message' => 'Verbose output!']))->save();
        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Shutdown initiated']))->save();
        (new LogEntry(['level' => LogLevel::WARNING, 'message' => 'Please be cautious']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(6, $stats->logEntries());
        $this->assertEquals(3, $stats->logEntries(LogLevel::INFO));
        $this->assertEquals(1, $stats->logEntries(LogLevel::DEBUG));
    }

    public function testPasswordResets(): void
    {
        $this->addUsers();

        (new PasswordReset(['user_id' => 1, 'token' => 'loremIpsum123']))->save();
        (new PasswordReset(['user_id' => 3, 'token' => '5omeR4nd0mTok3N']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->passwordResets());
    }

    /**
     * Add some example users
     */
    protected function addUsers(): void
    {
        $this->addUser();
        $this->addUser([], ['shirt_size' => 'L'], ['email_human' => true, 'email_shiftinfo' => true]);
        $this->addUser(
            ['arrival_date' => Carbon::now()],
            [],
            ['email_human' => true, 'email_goodie' => true, 'email_news' => true]
        );
        $this->addUser(
            ['arrival_date' => Carbon::now()],
            ['pronoun' => 'unicorn'],
            ['language' => 'lo_RM', 'email_shiftinfo' => true]
        );
        $this->addUser(
            ['arrival_date' => Carbon::now(), 'got_voucher' => 2],
            ['shirt_size' => 'XXL'],
            ['language' => 'lo_RM']
        );
        $this->addUser(
            ['arrival_date' => Carbon::now(), 'got_voucher' => 9, 'force_active' => true, 'user_info' => 'Info'],
            [],
            ['theme' => 1],
            ['drive_car' => true, 'drive_12t' => true, 'drive_confirmed' => true, 'ifsg_certificate_light' => true]
        );
        $this->addUser(
            ['arrival_date' => Carbon::now(), 'got_voucher' => 3, 'force_food' => true],
            ['pronoun' => 'per'],
            ['theme' => 1, 'email_human' => true],
            [
                'has_car' => true,
                'drive_forklift' => true,
                'drive_car' => true,
                'ifsg_certificate' => true,
                'ifsg_confirmed' => true,
            ]
        );
        $this->addUser(['arrival_date' => Carbon::now(), 'active' => 1, 'got_goodie' => true, 'force_active' => true]);
        $this->addUser(
            ['arrival_date' => Carbon::now(), 'active' => 1, 'got_goodie' => true, 'force_food' => true],
            ['shirt_size' => 'L'],
            ['theme' => 4]
        );
    }

    protected function addUser(
        array $state = [],
        array $personalData = [],
        array $settings = [],
        array $license = []
    ): User {
        $name = 'user_' . Str::random(5);

        $user = new User([
            'name'     => $name,
            'password' => '',
            'email'    => $name . '@engel.example.com',
            'api_key'  => '',
        ]);
        $user->save();

        $state = new State($state);
        $state->user()
            ->associate($user)
            ->save();

        $personalData = new PersonalData($personalData);
        $personalData->user()
            ->associate($user)
            ->save();

        $settings = new Settings(array_merge([
            'language'        => 'te_ST',
            'theme'           => 0,
            'email_human'     => false,
            'email_shiftinfo' => false,
        ], $settings));
        $settings->user()
            ->associate($user)
            ->save();

        $license = new License($license);
        $license->user()
            ->associate($user)
            ->save();

        return $user;
    }

    /**
     * Set up the environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
    }
}
