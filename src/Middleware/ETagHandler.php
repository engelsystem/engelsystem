<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Illuminate\Support\Str;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

class ETagHandler implements MiddlewareInterface
{
    /**
     * Compare the response ETag to a requested If-None-Match header and send a 304 "not modified" if they match
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $etagMatch = $request->getHeader('If-None-Match');
        $etag = $response->getHeader('ETag');

        if (
            !$etagMatch
            || !$etag
            || !Str::contains(implode(', ', $etagMatch), trim($etag[0], '"'))
        ) {
            return $response;
        }

        return $response
            ->withStatus(Response::HTTP_NOT_MODIFIED)
            ->withBody(Stream::create());
    }
}
