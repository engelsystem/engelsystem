<?php

namespace Engelsystem\Http;

use Engelsystem\Renderer\Renderer;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Response extends SymfonyResponse implements ResponseInterface
{
    use MessageTrait;

    public function __construct(
        string $content = '',
        int $status = 200,
        array $headers = [],
        protected ?Renderer $renderer = null,
        protected ?SessionInterface $session = null
    ) {
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
    public function withStatus(mixed $code, mixed $reasonPhrase = ''): static
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
    public function getReasonPhrase(): string
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
    public function withContent(mixed $content): static
    {
        $new = clone $this;
        $new->setContent($content);

        return $new;
    }

    /**
     * Return an instance with the rendered content.
     *
     * This method retains the immutability of the message and returns
     * an instance with the updated status and headers
     *
     * @param string[]|string[][] $headers
     */
    public function withView(string $view, array $data = [], int $status = 200, array $headers = []): Response
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
     */
    public function redirectTo(string $path, int $status = 302, array $headers = []): Response
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
     */
    public function setRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * Sets a session attribute (which is mutable)
     */
    public function with(string $key, mixed $value): Response
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
     */
    public function withInput(array $input): Response
    {
        if (!$this->session instanceof SessionInterface) {
            throw new InvalidArgumentException('Session not defined');
        }

        $this->session->set('form-data', $input);

        return $this;
    }
}
