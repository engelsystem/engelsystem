<?php

declare(strict_types=1);

namespace Engelsystem\Database;

use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;

/** @deprecated */
class Db
{
    protected static CapsuleManager $dbManager;

    /**
     * Set the database connection manager
     */
    public static function setDbManager(CapsuleManager $dbManager): void
    {
        self::$dbManager = $dbManager;
    }

    /**
     * Run a select query
     *
     * @return array[]
     */
    public static function select(string $query, array $bindings = []): array
    {
        $return = self::connection()->select($query, $bindings);

        // @TODO: Remove type casting
        foreach ($return as $key => $value) {
            $return[$key] = (array) $value;
        }

        return $return;
    }

    /**
     * Run a select query and return only the first result or null if no result is found.
     *
     * @return array|null
     */
    public static function selectOne(string $query, array $bindings = []): ?array
    {
        $result = self::connection()->selectOne($query, $bindings);

        // @TODO: remove typecast
        $result = (array) $result;
        if (empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Run an insert query
     */
    public static function insert(string $query, array $bindings = []): bool
    {
        return self::connection()->insert($query, $bindings);
    }

    /**
     * Run an update query
     */
    public static function update(string $query, array $bindings = []): int
    {
        return self::connection()->update($query, $bindings);
    }

    /**
     * Run a delete query
     */
    public static function delete(string $query, array $bindings = []): int
    {
        return self::connection()->delete($query, $bindings);
    }

    public static function connection(): DatabaseConnection
    {
        return self::$dbManager->getConnection();
    }

    /**
     * Get the PDO instance
     */
    public static function getPdo(): PDO
    {
        return self::connection()->getPdo();
    }
}
