<?php

namespace Engelsystem\Test\Unit\Models\User;

use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\Models\ModelTest;
use Exception;
use Illuminate\Support\Str;

class UserTest extends ModelTest
{
    use ArraySubsetAsserts;

    /** @var string[] */
    protected $data = [
        'name'     => 'lorem',
        'password' => '',
        'email'    => 'foo@bar.batz',
        'api_key'  => '',
    ];

    /**
     * @return array
     */
    public function hasOneRelationsProvider()
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
     * @covers       \Engelsystem\Models\User\User::contact
     * @covers       \Engelsystem\Models\User\User::license
     * @covers       \Engelsystem\Models\User\User::personalData
     * @covers       \Engelsystem\Models\User\User::settings
     * @covers       \Engelsystem\Models\User\User::state
     *
     * @dataProvider hasOneRelationsProvider
     *
     * @param string $class
     * @param string $name
     * @param array  $data
     * @throws Exception
     */
    public function testHasOneRelations($class, $name, $data)
    {
        $user = new User($this->data);
        $user->save();

        /** @var HasUserModel $contact */
        $contact = new $class($data);
        $contact->user()
            ->associate($user)
            ->save();

        $this->assertArraySubset($data, (array)$user->{$name}->attributesToArray());
    }

    /**
     * @covers       \Engelsystem\Models\User\User::news()
     *
     * @dataProvider hasManyRelationsProvider
     *
     * @param string $class     Class name of the related models
     * @param string $name      Name of the accessor for the related models
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
                ]
            ]
        ];
    }

    /**
     * Tests that accessing the NewsComments of an User works.
     *
     * @covers \Engelsystem\Models\User\User::newsComments
     */
    public function testNewsComments(): void
    {
        ($user = new User($this->data))->save();
        $newsComment = NewsComment::create(['news_id' => 0, 'text' => 'test comment', 'user_id' => $user->id]);
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
     * @covers \Engelsystem\Models\User\User::worklogs
     */
    public function testWorklogs(): void
    {
        ($user = new User($this->data))->save();
        $worklogEntry = Worklog::create([
            'user_id'    => $user->id,
            'creator_id' => $user->id,
            'hours'      => 1,
            'comment'    => '',
            'worked_at'  => Carbon::now(),
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
            'user_id'    => $user->id,
            'creator_id' => $user->id,
            'hours'      => 1,
            'comment'    => '',
            'worked_at'  => Carbon::now(),
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
}
