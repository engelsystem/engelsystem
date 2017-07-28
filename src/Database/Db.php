<?php

namespace Engelsystem\Database;

use PDO;
use PDOException;
use PDOStatement;

class Db
{
    /** @var PDO */
    protected static $db;

    /** @var PDOStatement */
    protected static $stm = null;

    /** @var bool */
    protected static $lastStatus = true;

    /**
     * Connect to database
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array  $options
     * @return bool
     */
    public static function connect($dsn, $username = null, $password = null, $options = [])
    {
        try {
            self::$db = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Run a prepared query
     *
     * @param string $query
     * @param array  $bindings
     * @return PDOStatement
     */
    public static function query($query, array $bindings = [])
    {
        self::$stm = self::$db->prepare($query);
        self::$lastStatus = self::$stm->execute($bindings);

        return self::$stm;
    }

    /**
     * Run a sql query
     *
     * @param string $query
     * @return bool
     */
    public static function unprepared($query)
    {
        self::$stm = self::$db->query($query);
        self::$lastStatus = (self::$stm instanceof PDOStatement);

        return self::$lastStatus;
    }

    /**
     * Run a select query
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public static function select($query, array $bindings = [])
    {
        self::query($query, $bindings);

        return self::$stm->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Run a select query and return only the first result or null if no result is found.
     * @param string $query
     * @param array  $bindings
     * @return array|null
     */
    public static function selectOne($query, array $bindings = [])
    {
        $result = self::select($query, $bindings);
        
        if(empty($result)) {
            return null;
        }
        
        return array_shift($result);
    }

    /**
     * Run an insert query
     *
     * @param string $query
     * @param array  $bindings
     * @return int Row count
     */
    public static function insert($query, array $bindings = [])
    {
        self::query($query, $bindings);

        return self::$stm->rowCount();
    }

    /**
     * Run an update query
     *
     * @param string $query
     * @param array  $bindings
     * @return int
     */
    public static function update($query, array $bindings = [])
    {
        self::query($query, $bindings);

        return self::$stm->rowCount();
    }

    /**
     * Run a delete query
     *
     * @param string $query
     * @param array  $bindings
     * @return int
     */
    public static function delete($query, array $bindings = [])
    {
        self::query($query, $bindings);

        return self::$stm->rowCount();
    }

    /**
     * Run a single statement
     *
     * @param string $query
     * @param array  $bindings
     * @return bool
     */
    public static function statement($query, array $bindings = [])
    {
        self::query($query, $bindings);

        return self::$lastStatus;
    }

    /**
     * Returns the last error
     *
     * @return array
     */
    public static function getError()
    {
        if (!self::$stm instanceof PDOStatement) {
            return [-1, null, null];
        }

        return self::$stm->errorInfo();
    }

    /**
     * Get the PDO instance
     *
     * @return PDO
     */
    public static function getPdo()
    {
        return self::$db;
    }

    /**
     * @return PDOStatement|false|null
     */
    public static function getStm()
    {
        return self::$stm;
    }
}
