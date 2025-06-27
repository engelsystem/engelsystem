<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\OAuth2;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class OAuth2Test extends TestCase
{
    use HasDatabase;

    /** @var AngelType[] */
    protected array $angelTypes;

    protected Authenticator | MockObject $auth;

    protected Config $config;

    protected TestLogger $log;

    protected User $user;

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::login
     * @covers \Engelsystem\Events\Listener\OAuth2::syncTeams
     * @covers \Engelsystem\Events\Listener\OAuth2::__construct
     */
    public function testLogin(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);

        $instance = new OAuth2($this->config, $this->log, $this->auth);
        $instance->login('oauth2.login', 'test-provider', collect(['groups_key' => ['/test', '/lorem']]));

        $user = User::find(1);
        $userAngelTypes = $user->userAngelTypes;
        $this->assertCount(2, $userAngelTypes);
        $this->assertTrue($this->log->hasInfoRecords());

        /** @var AngelType $test */
        $test = $userAngelTypes->where('pivot.angel_type_id', 21)->first();
        $this->assertNotNull($test);
        $this->assertFalse($test->pivot->supporter);
        $this->assertNull($test->pivot->confirm_user_id);
        $this->assertTrue($this->log->hasInfoThatContains('Added to angel type'));

        /** @var AngelType $lorem */
        $lorem = $userAngelTypes->where('pivot.angel_type_id', 42)->first();
        $this->assertNotNull($lorem);
        $this->assertTrue($lorem->pivot->supporter);
        $this->assertEquals($user->id, $lorem->pivot->confirm_user_id);
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::login
     */
    public function testLoginNoProvider(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);

        $instance = new OAuth2($this->config, $this->log, $this->auth);
        $instance->login('oauth2.login', 'unavailable-provider', collect(['foo' => 'bar']));
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::login
     */
    public function testLoginNoMatchingGroups(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);

        $instance = new OAuth2($this->config, $this->log, $this->auth);
        $instance->login('oauth2.login', 'test-provider', collect(['groups_key' => ['/notMatching']]));
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::login
     * @covers \Engelsystem\Events\Listener\OAuth2::syncTeams
     */
    public function testLoginNoChanges(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);
        $this->user->userAngelTypes()->attach($this->angelTypes['test']);
        $this->user->userAngelTypes()->attach(
            $this->angelTypes['lorem'],
            ['supporter' => true, 'confirm_user_id' => $this->user->id]
        );

        $instance = new OAuth2($this->config, $this->log, $this->auth);
        $instance->login('oauth2.login', 'test-provider', collect(['groups_key' => ['/test', '/lorem']]));

        /** @var UserAngelType $test */
        $test = UserAngelType::find(1);
        $this->assertFalse($test->supporter);
        $this->assertNull($test->confirm_user_id);

        /** @var UserAngelType $test */
        $lorem = UserAngelType::find(2);
        $this->assertTrue($lorem->supporter);
        $this->assertEquals($this->user->id, $lorem->confirm_user_id);

        $this->assertEmpty($this->log->records);
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::login
     * @covers \Engelsystem\Events\Listener\OAuth2::syncTeams
     */
    public function testLoginChangeSupport(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);
        $this->user->userAngelTypes()->attach($this->angelTypes['test']);
        $this->user->userAngelTypes()->attach($this->angelTypes['lorem']);

        $instance = new OAuth2($this->config, $this->log, $this->auth);
        $instance->login('oauth2.login', 'test-provider', collect(['groups_key' => ['/lorem', '/test']]));

        /** @var UserAngelType $userAngelType */
        $userAngelType = UserAngelType::find(2);
        $this->assertTrue($userAngelType->supporter);
        $this->assertEquals($this->user->id, $userAngelType->confirm_user_id);

        $this->assertTrue($this->log->hasInfoThatContains('supporter'));
        $this->assertTrue($this->log->hasInfoThatContains('confirmed'));
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::getSsoTeams
     */
    public function testGetSsoTeamsNotConfigured(): void
    {
        $instance = new OAuth2($this->config, $this->log, $this->auth);

        $teams = $instance->getSsoTeams('NotExistentProvider');
        $this->assertEquals([], $teams);
    }

    /**
     * @covers \Engelsystem\Events\Listener\OAuth2::getSsoTeams
     */
    public function testGetSsoTeams(): void
    {
        $instance = new OAuth2($this->config, $this->log, $this->auth);

        $teams = $instance->getSsoTeams('test-provider');
        $this->assertEquals([
            '/test'  => ['id' => 21, 'supporter' => false],
            '/lorem' => ['id' => 42, 'supporter' => true],
        ], $teams);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->config = new Config(['oauth' => [
            'test-provider' => [
                'groups' => 'groups_key',
                'teams'  => [
                    '/test'  => 21,
                    '/lorem' => ['id' => 42, 'supporter' => true],
                ],
            ],
        ]]);
        $this->app->instance(Config::class, $this->config);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->auth = $this->createMock(Authenticator::class);

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;

        /** @var AngelType $angelType1 */
        $angelType1 = AngelType::factory()->create(['id' => 21, 'name' => 'Test Name']);
        $this->angelTypes['test'] = $angelType1;

        /** @var AngelType $angelType2 */
        $angelType2 = AngelType::factory()->create(['id' => 42, 'name' => 'Lorem Name']);
        $this->angelTypes['lorem'] = $angelType2;
    }
}
