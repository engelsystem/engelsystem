<?php

namespace Engelsystem\Test\Unit\Helpers\Stub;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class UserModelImplementation extends User
{
    /** @var User */
    public static $user = null;

    /** @var int */
    public static $id = null;

    /** @var int */
    public static $apiKey = null;

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

    /**
     * @param string $apiKey
     * @return User[]|Collection|\Illuminate\Database\Query\Builder
     */
    public static function whereApiKey($apiKey)
    {
        if ($apiKey != static::$apiKey) {
            throw new InvalidArgumentException('Wrong api key searched');
        }

        return new Collection([self::$user]);
    }
}
