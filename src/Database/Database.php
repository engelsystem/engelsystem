<?php

namespace Engelsystem\Database;

use Illuminate\Database\Connection as DatabaseConnection;
use PDO;

class Database
{
    public function __construct(protected DatabaseConnection $connection)
    {
    }

    /**
     * Run a select query
     *
     * @return object[]
     */
    public function select(string $query, array $bindings = []): array
    {
        return $this->connection->select($query, $bindings);
    }

    /**
     * Run a select query and return only the first result or null if no result is found.
     */
    public function selectOne(string $query, array $bindings = []): ?object
    {
        return $this->connection->selectOne($query, $bindings);
    }

    /**
     * Run an insert query
     */
    public function insert(string $query, array $bindings = []): bool
    {
        return $this->connection->insert($query, $bindings);
    }

    /**
     * Run an update query
     */
    public function update(string $query, array $bindings = []): int
    {
        return $this->connection->update($query, $bindings);
    }

    /**
     * Run a delete query
     */
    public function delete(string $query, array $bindings = []): int
    {
        return $this->connection->delete($query, $bindings);
    }

    /**
     * Get the PDO instance
     */
    public function getPdo(): PDO
    {
        return $this->connection->getPdo();
    }

    public function getConnection(): DatabaseConnection
    {
        return $this->connection;
    }
}
