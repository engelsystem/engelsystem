<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Exceptions\Handler;
use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ApiRouteHandler implements MiddlewareInterface
{
    public function __construct(
        protected ?string $apiPrefix = '/api',
        protected ?array $apiAccessiblePaths = [
            '/atom',
            '/rss',
            '/health',
            '/ical',
            '/metrics',
            '/shifts-json-export',
        ]
    ) {
    }

    /**
     * Process the incoming request and handling API responses
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = (new Uri((string) $request->getUri()))->getPath();
        if ($request instanceof Request) {
            $path = $request->getPathInfo();
        }

        $path = urldecode($path);
        $isApi = $this->apiPrefix && (Str::startsWith($path, $this->apiPrefix . '/') || $path == $this->apiPrefix);
        $isApiAccessible = $isApi || $this->apiAccessiblePaths && in_array($path, $this->apiAccessiblePaths);
        $request = $request
            ->withAttribute('route-api', $isApi)
            ->withAttribute('route-api-accessible', $isApiAccessible);

        return $isApi ? $this->processApi($request, $handler) : $handler->handle($request);
    }

    /**
     * Process the API request by ensuring that JSON is returned
     */
    protected function processApi(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (ModelNotFoundException) {
            $response = new Response('', 404);
            $response->setContent($response->getReasonPhrase());
        } catch (HttpException $e) {
            $response = new Response($e->getMessage(), $e->getStatusCode(), $e->getHeaders());
            $response->setContent($response->getContent() ?: $response->getReasonPhrase());
        } catch (Throwable $e) {
            /** @var Handler $handler */
            $handler = app('error.handler');
            $handler->exceptionHandler($e, false);
            $response = new Response('', 500);
            $response->setContent($response->getReasonPhrase());
        }

        if (!Str::isJson((string) $response->getBody())) {
            $content = (string) $response->getBody();
            $content = Stream::create(json_encode([
                'message' => $content,
            ]));
            $response = $response
                ->withHeader('content-type', 'application/json')
                ->withBody($content);
        }

        if (!$response->hasHeader('access-control-allow-origin')) {
            $response = $response->withHeader('access-control-allow-origin', '*');
        }

        $eTag = md5((string) $response->getBody());
        $response->setEtag($eTag);

        return $response;
    }
}
