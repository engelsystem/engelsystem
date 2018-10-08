<?php

namespace Engelsystem\Test\Unit\Helpers\Stub;

use Engelsystem\Models\User\User;
use InvalidArgumentException;

class UserModelImplementation extends User
{
    /** @var User */
    public static $user = null;

    /** @var int */
    public static $id = null;

    /**
     * @param mixed $id
     * @param array $columns
     * @return User|null
     */
    public static function find($id, $columns = ['*'])
    {
        if ($id != static::$id) {
            throw new InvalidArgumentException('Wrong user ID searched');
        }

        return self::$user;
    }
}
