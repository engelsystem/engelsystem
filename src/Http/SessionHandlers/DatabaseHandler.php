<?php

namespace Engelsystem\Http\SessionHandlers;

use Engelsystem\Database\Database;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DatabaseHandler extends AbstractHandler
{
    /** @var Database */
    protected $database;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function read($id): string
    {
        $session = $this->getQuery()
            ->where('id', '=', $id)
            ->first();

        return $session ? $session->payload : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data): bool
    {
        $values = [
            'payload'       => $data,
            'last_activity' => $this->getCurrentTimestamp(),
        ];

        $session = $this->getQuery()
            ->where('id', '=', $id)
            ->first();

        if (!$session) {
            return $this->getQuery()
                ->insert($values + [
                        'id' => $id,
                    ]);
        }

        $this->getQuery()
            ->where('id', '=', $id)
            ->update($values);

        // The update return can't be used directly because it won't change if the second call is in the same second
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id): bool
    {
        $this->getQuery()
            ->where('id', '=', $id)
            ->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxLifetime): bool
    {
        $timestamp = $this->getCurrentTimestamp(-$maxLifetime);

        $this->getQuery()
            ->where('last_activity', '<', $timestamp)
            ->delete();

        return true;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQuery(): QueryBuilder
    {
        return $this->database
            ->getConnection()
            ->table('sessions');
    }

    /**
     * Format the SQL timestamp
     *
     * @param int $diff
     * @return string
     */
    protected function getCurrentTimestamp(int $diff = 0): string
    {
        return date('Y-m-d H:i:s', strtotime(sprintf('%+d seconds', $diff)));
    }
}
