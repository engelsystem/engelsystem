<?php

namespace Engelsystem\Http;

class Redirector
{
    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var UrlGeneratorInterface */
    protected $url;

    /**
     * @param Request               $request
     * @param Response              $response
     * @param UrlGeneratorInterface $url
     */
    public function __construct(Request $request, Response $response, UrlGeneratorInterface $url)
    {
        $this->request = $request;
        $this->response = $response;
        $this->url = $url;
    }

    /**
     * Redirects to a path, generating a full URL
     *
     * @param string $path
     * @param int    $status
     * @param array  $headers
     * @return Response
     */
    public function to(string $path, int $status = 302, array $headers = []): Response
    {
        return $this->response->redirectTo($this->url->to($path), $status, $headers);
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
