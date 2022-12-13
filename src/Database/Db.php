<?php

namespace Engelsystem\Database;

use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;

/** @deprecated */
class Db
{
    /** @var CapsuleManager */
    protected static $dbManager;

    /**
     * Set the database connection manager
     *
     */
    public static function setDbManager(CapsuleManager $dbManager)
    {
        self::$dbManager = $dbManager;
    }

    /**
     * Run a select query
     *
     * @param array  $bindings
     * @return array[]
     */
    public static function select(string $query, array $bindings = [])
    {
        $return = self::connection()->select($query, $bindings);

        // @TODO: Remove type casting
        foreach ($return as $key => $value) {
            $return[$key] = (array)$value;
        }

        return $return;
    }

    /**
     * Run a select query and return only the first result or null if no result is found.
     *
     * @param array  $bindings
     * @return array|null
     */
    public static function selectOne(string $query, array $bindings = [])
    {
        $result = self::connection()->selectOne($query, $bindings);

        // @TODO: remove typecast
        $result = (array)$result;
        if (empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Run an insert query
     *
     * @param array  $bindings
     * @return bool
     */
    public static function insert(string $query, array $bindings = [])
    {
        return self::connection()->insert($query, $bindings);
    }

    /**
     * Run an update query
     *
     * @param array  $bindings
     * @return int
     */
    public static function update(string $query, array $bindings = [])
    {
        return self::connection()->update($query, $bindings);
    }

    /**
     * Run a delete query
     *
     * @param array  $bindings
     * @return int
     */
    public static function delete(string $query, array $bindings = [])
    {
        return self::connection()->delete($query, $bindings);
    }

    /**
     * @return DatabaseConnection
     */
    public static function connection()
    {
        return self::$dbManager->getConnection();
    }

    /**
     * Get the PDO instance
     *
     * @return PDO
     */
    public static function getPdo()
    {
        return self::connection()->getPdo();
    }
}
