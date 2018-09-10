<?php

namespace Engelsystem\Http;

use Engelsystem\Renderer\Renderer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse implements ResponseInterface
{
    use MessageTrait;

    /** @var Renderer */
    protected $view;

    /**
     * @param string   $content
     * @param int      $status
     * @param array    $headers
     * @param Renderer $view
     */
    public function __construct(
        $content = '',
        int $status = 200,
        array $headers = [],
        Renderer $view = null
    ) {
        $this->view = $view;
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
     * @throws \InvalidArgumentException For invalid status code arguments.
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
        $new->setContent($content);

        return $new;
    }

    /**
     * Return an instance with the rendered content.
     *
     * THis method retains the immutability of the message and returns
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
        if (!$this->view instanceof Renderer) {
            throw new \InvalidArgumentException('Renderer not defined');
        }

        return $this->create($this->view->render($view, $data), $status, $headers);
    }
}
