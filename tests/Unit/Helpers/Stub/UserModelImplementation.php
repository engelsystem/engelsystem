<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Stub;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;

class UserModelImplementation extends User
{
    public static ?User $user = null;

    public static ?int $id = null;

    public static ?string $apiKey = null;

    /**
     * @param mixed $id
     * @param array $columns
     */
    public function find($id, $columns = ['*']): ?User // phpcs:ignore
    {
        if ($id != static::$id) {
            throw new InvalidArgumentException('Wrong user ID searched');
        }

        return self::$user;
    }

    /**
     * @return User[]|Collection|QueryBuilder
     */
    public static function whereApiKey(string $value): array|Collection|QueryBuilder
    {
        if ($value != static::$apiKey) {
            throw new InvalidArgumentException('Wrong api key searched');
        }

        return new Collection([self::$user]);
    }
}
