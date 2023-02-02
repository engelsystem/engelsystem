<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Loader\LoaderInterface as TwigLoader;

class ErrorHandler implements MiddlewareInterface
{
    protected string $viewPrefix = 'errors/';

    /**
     * A list of inputs that are not saved from form input
     *
     * @var array<string>
     */
    protected array $formIgnore = [
        'password',
        'password_confirmation',
        'password2',
        'new_password',
        'new_password2',
        'new_pw',
        'new_pw2',
        '_token',
    ];

    public function __construct(protected TwigLoader $loader)
    {
    }

    /**
     * Handles any error messages
     *
     * Should be added at the beginning
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $response = $handler->handle($request);
        } catch (HttpException $e) {
            $response = $this->createResponse($e->getMessage(), $e->getStatusCode(), $e->getHeaders());
        } catch (ValidationException $e) {
            $response = $this->redirectBack();
            $response->with(
                'messages.' . NotificationType::ERROR->value,
                ['validation' => $e->getValidator()->getErrors()]
            );

            if ($request instanceof Request) {
                $response->withInput(Arr::except($request->request->all(), $this->formIgnore));
            }
        } catch (ModelNotFoundException $e) {
            $response = $this->createResponse('', 404);
        }

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeader('content-type');
        $contentType = array_shift($contentType);
        if (!$contentType && strpos($response->getBody()?->getContents() ?? '', '<html') !== false) {
            $contentType = 'text/html';
        }

        if (
            $statusCode < 400
            || !$response instanceof Response
            || !empty($contentType)
        ) {
            return $response;
        }

        $view = $this->selectView($statusCode);

        return $response->withView(
            $this->viewPrefix . $view,
            [
                'status'  => $statusCode,
                'content' => $response->getContent(),
            ],
            $statusCode,
            $response->getHeaders()
        );
    }

    /**
     * Select a view based on the given status code
     */
    protected function selectView(int $statusCode): string
    {
        $hundreds = intdiv($statusCode, 100);

        $viewsList = [$statusCode, $hundreds, $hundreds * 100];
        foreach ($viewsList as $view) {
            if ($this->loader->exists($this->viewPrefix . $view)) {
                return (string) $view;
            }
        }

        return 'default';
    }

    /**
     * Create a new response
     *
     * @codeCoverageIgnore
     */
    protected function createResponse(string $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        return response($content, $status, $headers);
    }

    /**
     * Create a redirect back response
     */
    protected function redirectBack(): Response
    {
        return back();
    }
}
