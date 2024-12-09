<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Factories;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Engelsystem\Config\Config;
use Engelsystem\Factories\User;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User as UserModel;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Engelsystem\Test\Utils\SignUpConfig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserTest extends ServiceProviderTest
{
    use HasDatabase;

    private User $subject;

    private Config $config;

    private SessionInterface $session;

    private CarbonImmutable $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::now();
        Carbon::setTestNow($this->now);

        $this->initDatabase();
        $this->config = new Config([]);
        $this->app->instance(Config::class, $this->config);
        $this->app->alias(Config::class, 'config');
        $this->config->set('oauth', []);
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance(SessionInterface::class, $this->session);
        $this->app->instance(LoggerInterface::class, new NullLogger());

        $this->app->instance(ServerRequestInterface::class, new Request());
        $this->app->instance(Authenticator::class, $this->app->make(Authenticator::class));
        $this->app->alias(Authenticator::class, 'authenticator');

        $this->subject = $this->app->make(User::class);
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();
    }

    /**
     * Minimal config with empty data.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testMinimumConfigEmpty(): void
    {
        SignUpConfig::setMinimumConfig($this->config);

        $this->assertDataRaisesValidationException(
            [],
            [
                'username' =>  [
                    'validation.username.required',
                    'validation.username.username',
                ],
                'email' =>  [
                    'validation.email.required',
                    'validation.email.email',
                ],
                'password' => [
                    'validation.password.required',
                    'validation.password.length',
                ],
                'password_confirmation' => [
                    'validation.password_confirmation.required',
                ],
            ]
        );
    }

    /**
     * Minimal config with valid data.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testMinimumConfigCreate(): void
    {
        SignUpConfig::setMinimumConfig($this->config);

        $user = $this->subject->createFromData([
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
        ]);

        $this->assertSame('fritz', $user->name);
        $this->assertSame('fritz@example.com', $user->email);
        $this->assertSame(false, $user->state->arrived);
        $this->assertNotEmpty($user->api_key);
    }

    /**
     * Maximum config with empty data.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testMaximumConfigEmpty(): void
    {
        SignUpConfig::setMaximumConfig($this->config);

        $this->assertDataRaisesValidationException(
            [],
            [
                'username' =>  [
                    'validation.username.required',
                    'validation.username.username',
                ],
                'email' =>  [
                    'validation.email.required',
                    'validation.email.email',
                ],
                'password' => [
                    'validation.password.required',
                    'validation.password.length',
                ],
                'password_confirmation' => [
                    'validation.password_confirmation.required',
                ],
                'planned_arrival_date' => [
                    'validation.planned_arrival_date.required',
                    'validation.planned_arrival_date.date',
                    'validation.planned_arrival_date.min',
                ],
                'tshirt_size' => [
                    'validation.tshirt_size.required',
                    'validation.tshirt_size.shirtSize',
                ],
            ]
        );
    }

    /**
     * Maximum config with invalid data.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testMaximumConfigInvalid(): void
    {
        SignUpConfig::setMaximumConfig($this->config);

        $this->assertDataRaisesValidationException(
            [
                'username' => 'fritz23',
                'pronoun' => str_repeat('a', 20),
                'firstname' => str_repeat('a', 70),
                'lastname' => str_repeat('a', 70),
                'email' => 'notanemail',
                'password' => 'a',
                'tshirt_size' => 'A',
                'planned_arrival_date' => $this->now->subDays(7)->format('Y-m-d'),
                'dect' => str_repeat('a', 50),
                'mobile' => str_repeat('a', 50),
            ],
            [
                'username' =>  [
                    'validation.username.username',
                ],
                'email' =>  [
                    'validation.email.email',
                ],
                'mobile' =>  [
                    'validation.mobile.length',
                ],
                'password' => [
                    'validation.password.length',
                ],
                'password_confirmation' => [
                    'validation.password_confirmation.required',
                ],
                'firstname' => [
                    'validation.firstname.length',
                ],
                'lastname' => [
                    'validation.lastname.length',
                ],
                'pronoun' => [
                    'validation.pronoun.max',
                ],
                'planned_arrival_date' => [
                    'validation.planned_arrival_date.min',
                ],
                'dect' =>  [
                    'validation.dect.length',
                ],
                'tshirt_size' => [
                    'validation.tshirt_size.shirtSize',
                ],
            ]
        );
    }

    /**
     * Minimal config with valid data.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testMaximumConfigCreate(): void
    {
        SignUpConfig::setMaximumConfig($this->config);

        $user = $this->subject->createFromData([
            'pronoun' => 'they',
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
            'planned_arrival_date' => $this->now->format('Y-m-d'),
            'tshirt_size' => 'M',
            'mobile_show' => 1,
            'email_system' => 1,
        ]);

        $this->assertSame('they', $user->personalData->pronoun);
        $this->assertSame('fritz', $user->name);
        $this->assertSame('fritz@example.com', $user->email);
        $this->assertTrue(password_verify('s3cret', $user->password));
        $this->assertSame(
            $this->now->format('Y-m-d'),
            $user->personalData->planned_arrival_date->format('Y-m-d')
        );
        $this->assertTrue($user->settings->mobile_show);
        $this->assertTrue($user->settings->email_shiftinfo);
        $this->assertTrue($user->settings->email_messages);
        $this->assertTrue($user->settings->email_news);
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testPasswordDoesNotMatchConfirmation(): void
    {
        SignUpConfig::setMinimumConfig($this->config);

        $this->assertDataRaisesValidationException(
            [
                'username' => 'fritz',
                'email' => 'fritz@example.com',
                'password' => 's3cret',
                'password_confirmation' => 'huhuuu',
            ],
            [
                'password' => [
                    'settings.password.confirmation-does-not-match',
                ],
            ]
        );
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testUsernameAlreadyTaken(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->createFritz();

        $this->assertDataRaisesValidationException(
            [
                'username' => 'fritz',
                'email' => 'fritz@example.com',
                'password' => 's3cret',
                'password_confirmation' => 's3cret',
            ],
            [
                'username' => [
                    'settings.profile.nick.already-taken',
                ],
            ]
        );
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testEmailAlreadyTaken(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->createFritz();

        $this->assertDataRaisesValidationException(
            [
                'username' => 'peter',
                'email' => 'fritz@example.com',
                'password' => 's3cret',
                'password_confirmation' => 's3cret',
            ],
            [
                'email' => [
                    'settings.profile.email.already-taken',
                ],
            ]
        );
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testAngelTypeAssignment(): void
    {
        $angelTypes = $this->createAngelTypes();
        SignUpConfig::setMinimumConfig($this->config);

        $user = $this->subject->createFromData([
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
            'angel_types_' . $angelTypes[0]->id => 1,
            'angel_types_' . $angelTypes[1]->id => 1,
            // some angel type, that does not exist
            'angel_types_asd' => 1,
        ]);

        // Expect an assignment of the normal angel type
        $this->assertTrue(
            $user->userAngelTypes->contains('name', $angelTypes[0]->name)
        );

        // Do not expect an assignment of the angel type hidden on registration
        $this->assertFalse(
            $user->userAngelTypes->contains('name', $angelTypes[1]->name)
        );
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testDisablePasswortViaOAuth(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->config->set('enable_password', false);
        $this->session->set('oauth2_enable_password', true);

        $user = $this->subject->createFromData([
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
        ]);

        $this->assertSame('fritz', $user->name);
        $this->assertSame('fritz@example.com', $user->email);
        $this->assertTrue(password_verify('s3cret', $user->password));
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testAutoArrive(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->config->set('autoarrive', true);

        $user = $this->subject->createFromData([
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
        ]);

        $this->assertSame('fritz', $user->name);
        $this->assertSame('fritz@example.com', $user->email);
        $this->assertSame(true, $user->state->arrived);
        $this->assertEqualsWithDelta(
            $this->now->timestamp,
            $user->state->arrival_date->timestamp,
            1,
        );
    }

    /**
     * Covers the case where both, build-up and tear-down dates are configured.
     *
     * @covers \Engelsystem\Factories\User
     */
    public function testBuildUpAndTearDownDates(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->config->set('enable_planned_arrival', true);
        $this->config->set('buildup_start', $this->now);
        $this->config->set('teardown_end', $this->now->addDays(7));

        $this->assertDataRaisesValidationException(
            [
                'username' => 'fritz',
                'email' => 'fritz@example.com',
                'password' => 's3cret',
                'password_confirmation' => 's3cret',
                'planned_arrival_date' => $this->now->subDays(7)->format('Y-m-d'),
            ],
            [
                'planned_arrival_date' =>  [
                    'validation.planned_arrival_date.between',
                ],
            ]
        );
    }

    /**
     * @covers \Engelsystem\Factories\User
     */
    public function testOAuth(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $this->session->set('oauth2_connect_provider', 'sso');
        $this->session->set('oauth2_user_id', 'fritz_sso');
        $this->session->set('oauth2_access_token', 'abc123');
        $this->session->set('oauth2_refresh_token', 'jkl456');
        $this->session->set('oauth2_expires_at', '2023-08-15 08:00:00');

        $user = $this->subject->createFromData([
            'username' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => 's3cret',
            'password_confirmation' => 's3cret',
        ]);

        $oAuth = $user->oauth->first();
        $this->assertNotNull($oAuth);
        $this->assertSame('sso', $oAuth->provider);
        $this->assertSame('fritz_sso', $oAuth->identifier);
        $this->assertSame('abc123', $oAuth->access_token);
        $this->assertSame('jkl456', $oAuth->refresh_token);
        $this->assertSame('2023-08-15 08:00:00', $oAuth->expires_at->format('Y-m-d H:i:s'));
    }

    /**
     * Create a user with nick "fritz" and email "fritz@example.com".
     */
    private function createFritz(): void
    {
        UserModel::create([
            'name' => 'fritz',
            'email' => 'fritz@example.com',
            'password' => '',
            'api_key' => '',
        ]);
    }

    /**
     * Creates two AngelTypes:
     * 1. Normal angel type
     * 2. Angel type hidden on registration
     *
     * @return Array<AngelType>
     */
    private function createAngelTypes(): array
    {
        return [
            AngelType::create([
                'name' => 'Test angel type 1',
            ]),
            AngelType::create([
                'name' => 'Test angel type 2',
                'hide_register' => true,
            ]),
        ];
    }

    /**
     * @param Array<string, mixed> $data Data passed to User::createFromData
     * @param Array<string, Array<string>> $expectedValidationErrors Expected validation errors
     */
    private function assertDataRaisesValidationException(array $data, array $expectedValidationErrors): void
    {
        try {
            $this->subject->createFromData($data);
            self::fail('Expected exception not raised');
        } catch (ValidationException $err) {
            $validator = $err->getValidator();
            $validationErrors = $validator->getErrors();
            $this->assertSame($expectedValidationErrors, $validationErrors);
        }
    }
}
