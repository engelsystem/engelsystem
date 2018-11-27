<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\BaseController;

class ControllerImplementation extends BaseController
{
    /** @var array */
    protected $permissions = [
        'foo',
        'lorem' => [
            'ipsum',
            'dolor',
        ],
    ];
}
