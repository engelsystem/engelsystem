<?php

declare(strict_types=1);

namespace Engelsystem\Http;

class Redirector
{
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected UrlGeneratorInterface $url
    ) {
    }

    /**
     * Redirects to a path, generating a full URL
     */
    public function to(string $path, int $status = 302, array $headers = []): Response
    {
        return $this->response->redirectTo($this->url->to($path), $status, $headers);
    }

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
