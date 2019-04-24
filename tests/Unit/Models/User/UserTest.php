<?php

namespace Engelsystem\Test\Unit\Models;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    use ArraySubsetAsserts;
    use HasDatabase;

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
     * Prepare test
     */
    protected function setUp(): void
    {
        $this->initDatabase();
    }
}
