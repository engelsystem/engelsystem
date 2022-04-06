<?php

namespace Engelsystem\Http;

use Engelsystem\Renderer\Renderer;
use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Response extends SymfonyResponse implements ResponseInterface
{
    use MessageTrait;

    /**
     * @var SessionInterface
     */
    protected $session;

    /** @var Renderer */
    protected $renderer;

    /** @var Stream */
    protected $stream;

    /**
     * @param string           $content
     * @param int              $status
     * @param array            $headers
     * @param Renderer         $renderer
     * @param SessionInterface $session
     */
    public function __construct(
        $content = '',
        int $status = 200,
        array $headers = [],
        Renderer $renderer = null,
        SessionInterface $session = null
    ) {
        $this->renderer = $renderer;
        $this->session = $session;
        $this->stream = Stream::create();

        parent::__construct($content, $status, $headers);
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->setStatusCode($code, !empty($reasonPhrase) ? $reasonPhrase : null);

        return $new;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->statusText;
    }

    /**
     * Return an instance with the specified content.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @param mixed $content Content that can be cast to string
     * @return static
     */
    public function withContent($content)
    {
        $new = clone $this;
        $new->getBody()->write((string) $content);

        return $new;
    }

    /**
     * Sets the response content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setContent(?string $content)
    {
        $this->getBody()->write((string) $content);
        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * Return an instance with the rendered content.
     *
     * This method retains the immutability of the message and returns
     * an instance with the updated status and headers
     *
     * @param string              $view
     * @param array               $data
     * @param int                 $status
     * @param string[]|string[][] $headers
     * @return Response
     */
    public function withView($view, $data = [], $status = 200, $headers = [])
    {
        if (!$this->renderer instanceof Renderer) {
            throw new InvalidArgumentException('Renderer not defined');
        }

        $new = clone $this;
        $new->setContent($this->renderer->render($view, $data));
        $new->setStatusCode($status, ($status == $this->getStatusCode() ? $this->statusText : null));

        foreach ($headers as $key => $values) {
            $new = $new->withAddedHeader($key, $values);
        }

        return $new;
    }

    /**
     * Return an redirect instance
     *
     * This method retains the immutability of the message and returns
     * an instance with the updated status and headers
     *
     * @param string $path
     * @param int    $status
     * @param array  $headers
     * @return Response
     */
    public function redirectTo($path, $status = 302, $headers = [])
    {
        $response = $this->withStatus($status);
        $response = $response->withHeader('location', $path);

        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }

    /**
     * Set the renderer to use
     *
     * @param Renderer $renderer
     */
    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Sets a session attribute (which is mutable)
     *
     * @param string        $key
     * @param mixed|mixed[] $value
     * @return Response
     */
    public function with(string $key, $value)
    {
        if (!$this->session instanceof SessionInterface) {
            throw new InvalidArgumentException('Session not defined');
        }

        $data = $this->session->get($key);
        if (is_array($data) && is_array($value)) {
            $value = array_merge_recursive($data, $value);
        }

        $this->session->set($key, $value);

        return $this;
    }

    /**
     * Sets form data to the mutable session
     *
     * @param array $input
     * @return Response
     */
    public function withInput(array $input)
    {
        if (!$this->session instanceof SessionInterface) {
            throw new InvalidArgumentException('Session not defined');
        }

        $this->session->set('form-data', $input);

        return $this;
    }
}
