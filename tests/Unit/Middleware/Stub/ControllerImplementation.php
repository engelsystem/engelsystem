<?php

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Engelsystem\Controllers\BaseController;

class ControllerImplementation extends BaseController
{
    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string
     */
    public function actionStub()
    {
        return '';
    }
}
