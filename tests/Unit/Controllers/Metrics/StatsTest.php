<?php

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use Carbon\Carbon;
use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Models\Faq;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Question;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;
use Psr\Log\LogLevel;

class StatsTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::__construct
     * @covers \Engelsystem\Controllers\Metrics\Stats::newUsers
     */
    public function testNewUsers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->newUsers());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::vouchers
     * @covers \Engelsystem\Controllers\Metrics\Stats::vouchersQuery
     */
    public function testVouchers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(14, $stats->vouchers());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::vouchersBuckets
     */
    public function testVouchersBuckets(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals([1 => 6, 3 => 8, '+Inf' => 9], $stats->vouchersBuckets([1, 3, '+Inf']));
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::tshirts
     */
    public function testTshirts(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->tshirts());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::tshirtSizes
     * @covers \Engelsystem\Controllers\Metrics\Stats::raw
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::languages
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::themes
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::licenses
     */
    public function testLicenses(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(1, $stats->licenses('has_car'));
        $this->assertEquals(1, $stats->licenses('forklift'));
        $this->assertEquals(2, $stats->licenses('car'));
        $this->assertEquals(0, $stats->licenses('3.5t'));
        $this->assertEquals(0, $stats->licenses('7.5t'));
        $this->assertEquals(1, $stats->licenses('12t'));
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::worklogSeconds
     */
    public function testWorklogSeconds(): void
    {
        $this->addUsers();
        $worklogData = [
            'user_id'    => 1,
            'creator_id' => 1,
            'hours'      => 2.4,
            'comment'    => '',
            'worked_at'  => new Carbon()
        ];
        (new Worklog($worklogData))->save();
        (new Worklog(['hours' => 1.2, 'user_id' => 3] + $worklogData))->save();

        $stats = new Stats($this->database);
        $seconds = $stats->worklogSeconds();

        $this->assertEquals(2.4 * 60 * 60 + 1.2 * 60 * 60, $seconds);
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::rooms
     */
    public function testRooms(): void
    {
        (new Room(['name' => 'Room 1']))->save();
        (new Room(['name' => 'Second room']))->save();
        (new Room(['name' => 'Another room']))->save();
        (new Room(['name' => 'Old room']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->rooms());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::announcements
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::comments
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::questions
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::arrivedUsers
     */
    public function testArrivedUsers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(7, $stats->arrivedUsers());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::forceActiveUsers
     */
    public function testForceActiveUsers(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->forceActiveUsers());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::usersPronouns
     */
    public function testUsersPronouns(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->usersPronouns());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::email
     */
    public function testEmail(): void
    {
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(0, $stats->email('not-available-option'));
        $this->assertEquals(2, $stats->email('system'));
        $this->assertEquals(3, $stats->email('humans'));
        $this->assertEquals(1, $stats->email('goody'));
        $this->assertEquals(1, $stats->email('news'));
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::faq
     */
    public function testFaq(): void
    {
        (new Faq(['question' => 'Foo?', 'text' => 'Bar!']))->save();
        (new Faq(['question' => 'Lorem??', 'text' => 'Ipsum!!!']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->faq());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::messages
     */
    public function testMessages(): void
    {
        $this->addUsers();

        (new Message(['user_id' => 1, 'receiver_id' => 2, 'text' => 'Ohi?']))->save();
        (new Message(['user_id' => 4, 'receiver_id' => 1, 'text' => 'Testing stuff?']))->save();
        (new Message(['user_id' => 2, 'receiver_id' => 3, 'text' => 'Nope!', 'read' => true]))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->messages());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::sessions
     * @covers \Engelsystem\Controllers\Metrics\Stats::getQuery
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::oauth
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::databaseRead
     * @covers \Engelsystem\Controllers\Metrics\Stats::databaseWrite
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::logEntries
     */
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

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::passwordResets
     */
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
        $this->addUser(['arrived' => 1], [], ['email_human' => true, 'email_goody' => true, 'email_news' => true]);
        $this->addUser(['arrived' => 1], ['pronoun' => 'unicorn'], ['language' => 'lo_RM', 'email_shiftinfo' => true]);
        $this->addUser(['arrived' => 1, 'got_voucher' => 2], ['shirt_size' => 'XXL'], ['language' => 'lo_RM']);
        $this->addUser(
            ['arrived' => 1, 'got_voucher' => 9, 'force_active' => true],
            [],
            ['theme' => 1],
            ['drive_car' => true, 'drive_12t' => true]
        );
        $this->addUser(
            ['arrived' => 1, 'got_voucher' => 3],
            ['pronoun' => 'per'],
            ['theme' => 1, 'email_human' => true],
            ['has_car' => true, 'drive_forklift' => true, 'drive_car' => true]
        );
        $this->addUser(['arrived' => 1, 'active' => 1, 'got_shirt' => true, 'force_active' => true]);
        $this->addUser(['arrived' => 1, 'active' => 1, 'got_shirt' => true], ['shirt_size' => 'L'], ['theme' => 4]);
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
