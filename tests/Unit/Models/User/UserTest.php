<?php

namespace Engelsystem\Test\Unit\Models;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\News;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Exception;

class UserTest extends TestCase
{
    use ArraySubsetAsserts;
    use HasDatabase;

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
        ];
    }

    /**
     * @covers       \Engelsystem\Models\User\User::contact
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
            $model = (new $class);
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
     * Prepare test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
    }
}
