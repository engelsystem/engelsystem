<?php

namespace Engelsystem\Http\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /** @var int */
    protected $statusCode;

    /** @var array */
    protected $headers = [];

    /**
     * @param int            $statusCode
     * @param string         $message
     * @param array          $headers
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        int $statusCode,
        string $message = '',
        array $headers = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->headers = $headers;
        $this->statusCode = $statusCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
