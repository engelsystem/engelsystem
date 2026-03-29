<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin\Stub;

use Engelsystem\Controllers\Admin\BaseConfigController;
use Engelsystem\Http\Request;

class BaseConfigControllerImplementation extends BaseConfigController
{
    public function getOptions(): array
    {
        return $this->options;
    }

    public function callGetPageData(string $page): array
    {
        return $this->getPageData($page);
    }

    public function callParseOptions(): void
    {
        $this->parseOptions();
    }

    public function validateFoo(Request $request, array $rules): array
    {
        return [];
    }
}
