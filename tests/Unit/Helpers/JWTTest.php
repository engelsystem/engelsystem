<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use DomainException;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\JWT;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Throwable;
use UnexpectedValueException;

#[CoversMethod(JWT::class, '__construct')]
#[CoversMethod(JWT::class, 'encode')]
#[CoversMethod(JWT::class, 'decode')]
class JWTTest extends TestCase
{
    protected Config $config;
    protected TestLogger $log;

    public function testEncodeDecode(): void
    {
        $jwt = $this->app->make(JWT::class);

        $token = $jwt->encode([
            'sub' => 'encode-decode-test',
            'data' => ['some', 'data'],
        ], 8);
        $this->assertNotEmpty($token);
        $this->assertEquals(2, Str::substrCount($token, '.'));

        $data = $jwt->decode($token);
        $this->assertObjectHasProperty('sub', $data);
        $this->assertObjectHasProperty('data', $data);
        $this->assertObjectHasProperty('iat', $data);
        $this->assertObjectHasProperty('exp', $data);
        $this->assertObjectHasProperty('jti', $data);

        $this->assertEquals('encode-decode-test', $data->sub);
        $this->assertEquals(['some', 'data'], $data->data);

        $iat = Carbon::createFromTimestamp($data->iat);
        $this->assertTrue($iat <= Carbon::now());

        $exp = Carbon::createFromTimestamp($data->exp);
        $this->assertTrue($exp > $iat);
        $this->assertTrue($exp <= Carbon::now()->addMinutes(9));
        $this->assertTrue($exp >= Carbon::now()->addMinutes(7));

        $jti = Carbon::createFromTimestamp($data->jti);
        $this->assertTrue(Str::length($jti) >= 16);
    }

    public function testEncodeRandomJti(): void
    {
        $jwt = $this->app->make(JWT::class);

        $data = $jwt->decode($jwt->encode([
            'sub' => 'timing-test',
            'jti' => 'some-predefined-value',
        ]));
        $this->assertNotEquals('some-predefined-value', $data->jti);
    }

    public function testEncodeTimesUnchanged(): void
    {
        $jwt = $this->app->make(JWT::class);

        $iat = Carbon::now()->subMinutes(3)->timestamp;
        $exp = Carbon::now()->addMinutes(20)->timestamp;

        $data = $jwt->decode($jwt->encode([
            'sub' => 'timing-test',
            'iat' => $iat,
            'exp' => $exp,
        ]));
        $this->assertEquals($iat, $data->iat);
        $this->assertEquals($exp, $data->exp);
    }

    public function testEncodeTimesTimeCap(): void
    {
        $jwt = $this->app->make(JWT::class);

        $iat = Carbon::now()->subMinutes(3)->timestamp;
        $expFuture = Carbon::now()->addHour()->timestamp;

        $data = $jwt->decode($jwt->encode([
            'sub' => 'timing-test',
            'iat' => $iat,
            'exp' => $expFuture,
        ]));
        $this->assertEquals($iat, $data->iat);
        $this->assertTrue($data->iat < $expFuture);
    }

    public function testEncodeTimesExpiresSetMinutesCap(): void
    {
        $jwt = $this->app->make(JWT::class);

        $iat = Carbon::now()->timestamp;
        $expFutureMax = Carbon::now()->addMinutes(42 + 1)->timestamp;

        $data = $jwt->decode($jwt->encode([
            'sub' => 'timing-test',
            'iat' => $iat,
        ], 999));
        $this->assertEquals($iat, $data->iat);
        $this->assertTrue($data->iat < $expFutureMax);
    }

    public function testEncodeTimesExpiresSetMinutes(): void
    {
        $jwt = $this->app->make(JWT::class);

        $iat = Carbon::now()->timestamp;
        $expFuture21 = Carbon::now()->addMinutes(20 + 1)->timestamp;
        $expFuture19 = Carbon::now()->addMinutes(20 - 1)->timestamp;
        $data = $jwt->decode($jwt->encode([
            'sub' => 'timing-test',
            'iat' => $iat,
        ], 20));
        $this->assertEquals($iat, $data->iat);
        $this->assertTrue($data->exp < $expFuture21);
        $this->assertTrue($data->exp > $expFuture19);
    }

    public function testEncodeNoSub(): void
    {
        $jwt = $this->app->make(JWT::class);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('JWT payload needs a "sub" key');

        $jwt->encode(['foo' => 'bar']);
    }

    public function testDecodeInvalidTime(): void
    {
        $jwt = $this->app->make(JWT::class);
        $payload = [
            'sub' => 'OUTaTIME',
            'iat' => Carbon::now()->subHour()->timestamp,
            'exp' => Carbon::now()->subMinute()->timestamp,
        ];
        $token = $jwt->encode($payload);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('jwt.invalid_time');
        $jwt->decode($token);
    }

    public function testDecodeWrongFormat(): void
    {
        $jwt = $this->app->make(JWT::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('jwt.wrong_format');
        try {
            $jwt->decode('IWantToBeARealToken');
        } catch (Throwable $e) {
            $this->log->hasWarningThatContains('JWT: ');
            throw $e;
        }
    }

    public function testDecodeInvalidToken(): void
    {
        $jwt = $this->app->make(JWT::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('jwt.code_error');
        try {
            $jwt->decode('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MiIsImlhdCI6MTIzNDU2Nzg5MH0.n0p3');
        } catch (Throwable $e) {
            $this->log->hasWarningThatContains('JWT: ');
            throw $e;
        }
    }

    public function testDecodeError(): void
    {
        $jwt = $this->app->get(JWT::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('');
        try {
            $jwt->decode('some.test.code');
        } catch (Throwable $e) {
            $this->log->hasErrorThatContains('JWT Error');
            throw $e;
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->config = new Config([
            'app_key' => 'SomeTestKeyThatIsNotSecureAtAllSoWeCanIgnoreIt',
            'jwt_algorithm' => 'HS256',
            'jwt_expiration_time' => 42,
        ]);
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);
    }
}
