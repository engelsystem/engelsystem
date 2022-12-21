<?php

namespace Engelsystem\Http\SessionHandlers;

use Engelsystem\Database\Database;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DatabaseHandler extends AbstractHandler
{
    public function __construct(protected Database $database)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id): string
    {
        $session = $this->getQuery()
            ->where('id', '=', $id)
            ->first();

        return $session ? $session->payload : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
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
    public function destroy(string $id): bool
    {
        $this->getQuery()
            ->where('id', '=', $id)
            ->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $timestamp = $this->getCurrentTimestamp(-$max_lifetime);

        return $this->getQuery()
            ->where('last_activity', '<', $timestamp)
            ->delete();
    }

    protected function getQuery(): QueryBuilder
    {
        return $this->database
            ->getConnection()
            ->table('sessions');
    }

    /**
     * Format the SQL timestamp
     */
    protected function getCurrentTimestamp(int $diff = 0): string
    {
        return date('Y-m-d H:i:s', strtotime(sprintf('%+d seconds', $diff)));
    }
}
