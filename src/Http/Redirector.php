<?php

namespace Engelsystem\Http;

class Redirector
{
    protected Request $request;

    protected Response $response;

    protected UrlGeneratorInterface $url;

    public function __construct(Request $request, Response $response, UrlGeneratorInterface $url)
    {
        $this->request = $request;
        $this->response = $response;
        $this->url = $url;
    }

    /**
     * Redirects to a path, generating a full URL
     *
     * @param array  $headers
     */
    public function to(string $path, int $status = 302, array $headers = []): Response
    {
        return $this->response->redirectTo($this->url->to($path), $status, $headers);
    }

    /**
     * @param array $headers
     */
    public function back(int $status = 302, array $headers = []): Response
    {
        return $this->to($this->getPreviousUrl(), $status, $headers);
    }

    protected function getPreviousUrl(): string
    {
        if ($header = $this->request->getHeader('referer')) {
            return array_pop($header);
        }

        return '/';
    }
}
