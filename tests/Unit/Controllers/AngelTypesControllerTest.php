<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\AngelTypesController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

#[CoversMethod(AngelTypesController::class, 'hasPermission')]
#[CoversMethod(AngelTypesController::class, '__construct')]
#[CoversMethod(AngelTypesController::class, 'about')]
#[CoversMethod(AngelTypesController::class, 'qrCode')]
#[CoversMethod(AngelTypesController::class, 'join')]
#[CoversMethod(AngelTypesController::class, 'qrJoinEnabled')]
#[CoversMethod(AngelTypesController::class, 'getAngelType')]
#[AllowMockObjectsWithoutExpectations]
class AngelTypesControllerTest extends ControllerTestCase
{
    use HasDatabase;

    public function testHasPermission(): void
    {
        $response = $this->createStub(Response::class);
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $request = (new Request())->withAttribute('angel_type_id', $angelType->id);
        $user = User::factory()->create();

        $controller = new AngelTypesController($response, $this->app->get(Config::class), $auth, new NullLogger());
        $this->assertFalse($controller->hasPermission($request, 'qrCode'));
        $this->assertFalse($controller->hasPermission($request, 'join'));
        $this->assertNull($controller->hasPermission($request, 'about'));

        $this->setExpects($auth, 'user', [], $user, $this->atLeastOnce());
        $this->assertTrue($controller->hasPermission($request, 'join'));

        $this->setExpects($auth, 'can', ['admin_user_angeltypes'], true);
        $this->assertTrue($controller->hasPermission($request, 'qrCode'));
    }

    public function testIndex(): void
    {
        $response = $this->createMock(Response::class);
        $auth = $this->createMock(Authenticator::class);

        $this->setExpects(
            $response,
            'withView',
            ['pages/angeltypes/about']
        );

        $controller = new AngelTypesController($response, new Config(), $auth, new NullLogger());
        $controller->about();
    }

    public function testQrCode(): void
    {
        $response = $this->createMock(Response::class);
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $request = (new Request())->withAttribute('angel_type_id', $angelType->id);

        $this->setExpects($response, 'withInput', [], $response);
        $this->setExpects($response, 'withView', ['pages/angeltypes/qr'], $response);

        $controller = new AngelTypesController($response, $this->app->get(Config::class), $auth, new NullLogger());
        $controller->qrCode($request);
    }

    public function testQrCodePost(): void
    {
        $response = $this->createMock(Response::class);
        $auth = $this->createMock(Authenticator::class);
        $urlGenerator = $this->createMock(UrlGenerator::class);
        $this->app->instance('http.urlGenerator', $urlGenerator);
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $redirect = $this->createMock(Redirector::class);
        $this->app->instance('redirect', $redirect);
        $log = new TestLogger();
        $request = (new Request())
            ->withAttribute('angel_type_id', $angelType->id)
            ->withMethod('post')
            ->withParsedBody([
                'minutes' => 42,
            ]);

        $token = null;
        $urlGenerator->expects($this->exactly(2))
            ->method('to')
            ->willReturnCallback(function ($path, $parameters) use ($angelType, $user, &$token) {
                if ($path === '/angeltypes') {
                    return '/angeltypes..';
                }

                $this->assertEquals('/angeltypes/' . $angelType->id . '/join', $path);
                $this->assertArrayHasKey('token', $parameters);
                $token = $parameters['token'];
                $data = (array) JWT::decode(
                    $token,
                    new Key($this->config->get('app_key'), $this->config->get('jwt_algorithm'))
                );
                $this->assertArrayHasKey('sub', $data);
                $this->assertEquals('join_angel_type', $data['sub']);
                $this->assertArrayHasKey('iat', $data);
                $this->assertArrayHasKey('exp', $data);
                $this->assertArrayHasKey('id', $data);
                $this->assertArrayHasKey('jti', $data);
                $this->assertEquals($angelType->id, $data['id']);
                $this->assertArrayHasKey('by', $data);
                $this->assertEquals($user->id, $data['by']);
                return '/url..';
            });
        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls($user, $user2);
        $this->setExpects($response, 'withInput', [], $response);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($response) {
                $this->assertEquals('pages/angeltypes/qr', $view);
                $this->assertArrayHasKey('angel_type', $data);
                $this->assertArrayHasKey('qr_data', $data);
                $this->assertEquals('/url..', $data['qr_data']);
                $this->assertArrayHasKey('qr_max_expiration_minutes', $data);
                $this->assertEquals(60 * 24 * 5, $data['qr_max_expiration_minutes']);
                return $response;
            });
        $this->setExpects($redirect, 'to', ['/angeltypes..'], $response);

        $controller = new AngelTypesController($response, $this->config, $auth, $log);
        $controller->setValidator(new Validator());

        $controller->qrCode($request);

        $request = (new Request(['token' => $token]))
            ->withAttribute('angel_type_id', $angelType->id);
        $controller->join($request);

        $this->assertTrue($log->hasInfoThatContains('Joined angel type'));
        $this->assertHasNotification('angeltype.add.success');

        /** @var AngelType $userAngelType */
        $userAngelType = $user2->userAngelTypes->first();
        $this->assertNotEmpty($userAngelType);
        $this->assertEquals($angelType->id, $userAngelType->id);
        $this->assertEquals($user->id, $userAngelType->pivot->confirm_user_id);
    }

    public function testJoinDecodeError(): void
    {
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $controller = new AngelTypesController(new Response(), $this->config, $auth, new NullLogger());
        $controller->setValidator(new Validator());

        $request = (new Request(['token' => 'some.test.code']))
            ->withAttribute('angel_type_id', $angelType->id);

        $this->expectException(HttpNotFound::class);
        $controller->join($request);
    }

    public function testJoinInvalidSignatureError(): void
    {
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $controller = new AngelTypesController(new Response(), $this->config, $auth, new NullLogger());
        $controller->setValidator(new Validator());

        $request = (new Request([
            'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MiIsImlhdCI6MTIzNDU2Nzg5MH0.n0p3',
        ]))
            ->withAttribute('angel_type_id', $angelType->id);

        $this->expectException(HttpNotFound::class);
        $this->expectExceptionMessage('jwt.code_error');
        $controller->join($request);
    }

    public function testJoinDecodeErrorMissingParts(): void
    {
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $controller = new AngelTypesController(new Response(), $this->config, $auth, new NullLogger());
        $controller->setValidator(new Validator());

        $request = (new Request(['token' => 'missing.segment']))
            ->withAttribute('angel_type_id', $angelType->id);

        $this->expectException(HttpNotFound::class);
        $this->expectExceptionMessage('jwt.wrong_format');
        $controller->join($request);
    }

    public function testJoinDecodeExpired(): void
    {
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $token = JWT::encode(
            ['exp' => Carbon::now()->subMinute()->timestamp],
            $this->config->get('app_key'),
            $this->config->get('jwt_algorithm'),
        );

        $controller = new AngelTypesController(new Response(), $this->config, $auth, new NullLogger());
        $controller->setValidator(new Validator());

        $request = (new Request(['token' => $token]))
            ->withAttribute('angel_type_id', $angelType->id);

        $this->expectException(HttpNotFound::class);
        $this->expectExceptionMessage('jwt.invalid');
        $controller->join($request);
    }

    public function testJoinTokenMismatch(): void
    {
        $auth = $this->createMock(Authenticator::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $token = JWT::encode(
            ['sub' => 'do_something_different'],
            $this->config->get('app_key'),
            $this->config->get('jwt_algorithm'),
        );

        $controller = new AngelTypesController(new Response(), $this->config, $auth, new NullLogger());
        $controller->setValidator(new Validator());

        $request = (new Request(['token' => $token]))
            ->withAttribute('angel_type_id', $angelType->id);

        $this->expectException(HttpNotFound::class);
        $controller->join($request);
    }

    public function testGetAngelTypeNotFound(): void
    {
        $response = $this->createMock(Response::class);
        $auth = $this->createMock(Authenticator::class);
        $request = $this->createMock(Request::class);

        $controller = new AngelTypesController($response, $this->app->get(Config::class), $auth, new NullLogger());

        $this->expectException(ModelNotFoundException::class);
        $controller->qrCode($request);
    }

    public function testQrJoinEnabledDisabled(): void
    {
        $response = $this->createMock(Response::class);
        $auth = $this->createMock(Authenticator::class);
        $request = $this->createMock(Request::class);
        $this->config->set('join_qr_code', false);

        $controller = new AngelTypesController($response, $this->config, $auth, new NullLogger());

        $this->expectException(HttpNotFound::class);
        $controller->qrCode($request);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->config->set([
            'app_key' => 'S0me5ecUreTes1K3y',
            'jwt_algorithm' => 'HS256',
            'jwt_expiration_time' => 60 * 24 * 5,
        ]);
    }
}
