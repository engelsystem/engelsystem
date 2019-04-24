<?php

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements MiddlewareInterface
{
    use ResolvesMiddlewareTrait;

    /** @var Application */
    protected $container;

    /**
     * @param Application $container
     */
    public function __construct(Application $container)
    {
        $this->container = $container;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute('route-request-handler');
        $requestHandler = $this->resolveRequestHandler($requestHandler);

        if ($requestHandler instanceof CallableHandler) {
            $callable = $requestHandler->getCallable();

            if (is_array($callable) && $callable[0] instanceof BaseController) {
                $this->checkPermissions($callable[0], $callable[1]);
            }
        }

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        throw new InvalidArgumentException('Unable to process request handler of type ' . gettype($requestHandler));
    }

    /**
     * Resolve the given class
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface $handler
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    protected function resolveRequestHandler($handler)
    {
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
     *
     * @param BaseController $controller
     * @param string         $method
     * @return bool
     */
    protected function checkPermissions(BaseController $controller, string $method): bool
    {
        /** @var Authenticator $auth */
        $auth = $this->container->get('auth');
        $permissions = $controller->getPermissions();

        // Merge action permissions
        if (isset($permissions[$method])) {
            $permissions = array_merge($permissions, (array)$permissions[$method]);
        }

        foreach ($permissions as $key => $permission) {
            // Skip all action permission entries
            if (!is_int($key)) {
                continue;
            }

            if (!$auth->can($permission)) {
                throw new HttpForbidden();
            }
        }

        return true;
    }
}
