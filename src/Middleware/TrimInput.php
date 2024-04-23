<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request middleware that trims all string values of PUT and POST requests.
 * Some fields such as passwords are excluded.
 */
class TrimInput implements MiddlewareInterface
{
    /**
     * @var array<string> List of field names to exclude from trim
     */
    private const TRIM_EXCLUDE_LIST = [
        'password',
        'password2',
        'new_password',
        'new_password2',
        'new_pw',
        'new_pw2',
        'password_confirmation',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['PUT', 'POST']) && is_array($request->getParsedBody())) {
            $trimmedParsedBody = $this->trimArrayValues($request->getParsedBody());
            $request = $request->withParsedBody($trimmedParsedBody);
        }

        return $handler->handle($request);
    }

    /**
     * @template AK array key type
     * @template AV array value type
     * @param array<AK, AV> $array
     * @return array<AK, AV>
     */
    private function trimArrayValues(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // recurse trim
                $result[$key] = $this->trimArrayValues($value);
                continue;
            }

            if (is_string($value) && !in_array($key, self::TRIM_EXCLUDE_LIST)) {
                // trim only non-excluded string values
                $result[$key] = trim($value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
