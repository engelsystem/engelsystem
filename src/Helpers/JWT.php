<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use DomainException;
use Engelsystem\Config\Config;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as JWTLib;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use stdClass;
use UnexpectedValueException;

class JWT
{
    protected string $key;
    protected string $alg;
    protected int $expire_max;

    public function __construct(
        Config $config,
        protected LoggerInterface $log,
    ) {
        $this->key = (string) $config->get('app_key');
        $this->alg = (string) $config->get('jwt_algorithm');
        $this->expire_max = (int) $config->get('jwt_expiration_time');
    }

    public function encode(array $payload, int $expires = 5): string
    {
        $now = Carbon::now();
        $timeMax = (clone $now)->addMinutes($this->expire_max)->timestamp;
        $jti = Str::random();

        $iat = array_key_exists('iat', $payload) ? $payload['iat'] : $now->timestamp;
        $exp = array_key_exists('exp', $payload)
            ? $payload['exp']
            : Carbon::createFromTimestamp($iat)->addMinutes(min($expires, $this->expire_max))->timestamp;

        if (!array_key_exists('sub', $payload)) {
            throw new UnexpectedValueException('JWT payload needs a "sub" key');
        }

        if ($exp > $timeMax) {
            $exp = $timeMax;
        }

        $payload = array_merge($payload, [
            'iat' => $iat,
            'exp' => $exp,
            'jti' => $jti,
        ]);

        return JWTLib::encode($payload, $this->key, $this->alg);
    }

    public function decode(string $jwt): stdClass
    {
        try {
            $decoded = JWTLib::decode($jwt, new Key($this->key, $this->alg));
        } catch (BeforeValidException | ExpiredException $e) {
            throw new DomainException('jwt.invalid_time', 0, $e);
        } catch (UnexpectedValueException | SignatureInvalidException $e) {
            $this->log->warning('JWT: {message}, token: "{token}"', ['message' => $e->getMessage(), 'token' => $jwt]);
            if ($e->getMessage() === 'Wrong number of segments') {
                throw new DomainException('jwt.wrong_format', 0, $e);
            }
            throw new DomainException('jwt.code_error', 0, $e);
        } catch (Exception $e) {
            $this->log->error('JWT Error, token: "{token}"', ['exception' => $e, 'token' => $jwt]);
            throw new DomainException('', 0, $e);
        }

        return $decoded;
    }
}
