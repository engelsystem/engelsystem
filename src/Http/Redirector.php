<?php

namespace Engelsystem\Http;

class Redirector
{
    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param string $path
     * @param int    $status
     * @param array  $headers
     * @return Response
     */
    public function to(string $path, int $status = 302, array $headers = []): Response
    {
        return $this->response->redirectTo($path, $status, $headers);
    }

    /**
     * @param int   $status
     * @param array $headers
     * @return Response
     */
    public function back(int $status = 302, array $headers = []): Response
    {
        return $this->to($this->getPreviousUrl(), $status, $headers);
    }

    /**
     * @return string
     */
    protected function getPreviousUrl(): string
    {
        if ($header = $this->request->getHeader('referer')) {
            return array_pop($header);
        }

        return '/';
    }
}
