<?php

namespace Engelsystem\Test\Unit\Helpers\Stub;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
     * @param array $columns
     * @return User|null
     */
    public function find(mixed $id, array $columns = ['*'])
    {
        if ($id != static::$id) {
            throw new InvalidArgumentException('Wrong user ID searched');
        }

        return self::$user;
    }

    /**
     * @return User[]|Collection|QueryBuilder
     */
    public static function whereApiKey(string $apiKey)
    {
        if ($apiKey != static::$apiKey) {
            throw new InvalidArgumentException('Wrong api key searched');
        }

        return new Collection([self::$user]);
    }
}
