<?php

namespace Engelsystem\Middleware;

use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig_LoaderInterface as TwigLoader;

class ErrorHandler implements MiddlewareInterface
{
    /** @var TwigLoader */
    protected $loader;

    /** @var string */
    protected $viewPrefix = 'errors/';

    /**
     * A list of inputs that are not saved from form input
     *
     * @var array
     */
    protected $formIgnore = [
        'password',
        'password_confirmation',
        'password2',
        'new_password',
        'new_password2',
        'new_pw',
        'new_pw2',
        '_token',
    ];

    /**
     * @param TwigLoader $loader
     */
    public function __construct(TwigLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Handles any error messages
     *
     * Should be added at the beginning
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
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
            $response = $this->createResponse('', 302, ['Location' => $this->getPreviousUrl($request)]);

            if ($request instanceof Request) {
                $session = $request->getSession();
                $session->set(
                    'errors',
                    array_merge_recursive(
                        $session->get('errors', []),
                        ['validation' => $e->getValidator()->getErrors()]
                    )
                );

                $session->set('form-data', Arr::except($request->request->all(), $this->formIgnore));
            }
        }

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeader('content-type');
        $contentType = array_shift($contentType);
        if (!$contentType && strpos($response->getBody(), '<html') !== false) {
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
     *
     * @param int $statusCode
     * @return string
     */
    protected function selectView(int $statusCode): string
    {
        $hundreds = intdiv($statusCode, 100);

        $viewsList = [$statusCode, $hundreds, $hundreds * 100];
        foreach ($viewsList as $view) {
            if ($this->loader->exists($this->viewPrefix . $view)) {
                return $view;
            }
        }

        return 'default';
    }

    /**
     * Create a new response
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     * @return Response
     * @codeCoverageIgnore
     */
    protected function createResponse(string $content = '', int $status = 200, array $headers = [])
    {
        return response($content, $status, $headers);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getPreviousUrl(ServerRequestInterface $request)
    {
        if ($header = $request->getHeader('referer')) {
            return array_pop($header);
        }

        return '/';
    }
}
