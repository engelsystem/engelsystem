<?php

declare(strict_types=1);

namespace Engelsystem\Logger;

use Engelsystem\Http\Request;

class UrlAwareLogger extends Logger
{
    protected ?Request $request = null;

    /**
     * Adds the current URL to the log message
     */
    public function createEntry(array $data): void
    {
        if ($this->request) {
            $data['url'] = $this->request->getUri();
        }
        parent::createEntry($data);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
