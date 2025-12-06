<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements MiddlewareInterface
{
    use ResolvesMiddlewareTrait;

    public function __construct(protected Application $container)
    {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * Implements basic permission checking if the controller supports it.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute('route-request-handler');
        $this->container->instance(ServerRequestInterface::class, $request);
        $this->container->instance('request', $request);

        /** @var CallableHandler|MiddlewareInterface|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->resolveRequestHandler($requestHandler);

        if ($requestHandler instanceof CallableHandler) {
            $callable = $requestHandler->getCallable();

            if (
                is_array($callable)
                && $callable[0] instanceof BaseController
                && !$this->checkPermissions($request, $callable[0], $callable[1])
            ) {
                throw new HttpForbidden();
            }
        }

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        /**
         * Is RequestHandlerInterface
         * @see RequestHandlerInterface
         */
        return $requestHandler->handle($request);
    }

    /**
     * Resolve the given class
     */
    protected function resolveRequestHandler(
        string|callable|MiddlewareInterface|RequestHandlerInterface $handler
    ): MiddlewareInterface|RequestHandlerInterface {
        if (is_string($handler) && mb_strpos($handler, '@') !== false) {
            list($class, $method) = explode('@', $handler, 2);
            if (!class_exists($class) && !$this->container->has($class)) {
                $class = sprintf('Engelsystem\\Controllers\\%s', $class);
            }

            $handler = [$class, $method];
        }

        if (
            is_array($handler)
            && is_string($handler[0])
            && (
                class_exists($handler[0])
                || $this->container->has($handler[0])
            )
        ) {
            $handler[0] = $this->container->make($handler[0]);
        }

        return $this->resolveMiddleware($handler);
    }

    /**
     * Check required page permissions
     */
    protected function checkPermissions(
        ServerRequestInterface $request,
        BaseController $controller,
        string $method
    ): bool {
        $hasPermission = $controller->hasPermission($request, $method);
        if (!is_null($hasPermission)) {
            return $hasPermission;
        }

        /** @var Authenticator $auth */
        $auth = $this->container->get('auth');
        $permissions = $controller->getPermissions();

        // Merge action permissions
        if (isset($permissions[$method])) {
            $permissions = array_merge($permissions, (array) $permissions[$method]);
        }

        foreach ($permissions as $key => $permission) {
            // Skip all action permission entries
            if (!is_int($key)) {
                continue;
            }

            foreach ((array) $permission as $value) {
                if (
                    Str::contains($value, '||')
                        ? !$auth->canAny(explode('||', $value))
                        : !$auth->can($permission)
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
