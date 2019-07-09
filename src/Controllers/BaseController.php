<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Validation\ValidatesRequest;

abstract class BaseController
{
    use ValidatesRequest;

    /** @var string[]|string[][] A list of Permissions required to access the controller or certain pages */
    protected $permissions = [];

    /**
     * Returns the list of permissions
     *
     * @return string[]|string[][]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
