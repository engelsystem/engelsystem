<?php

namespace Engelsystem\Controllers\Metrics;

use Engelsystem\Database\Database;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression as QueryExpression;

class Stats
{
    /** @var Database */
    protected $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * The number of not arrived users
     *
     * @param null $working
     * @return int
     */
    public function arrivedUsers($working = null): int
    {
        $query = $this
            ->getQuery('users')
            ->join('users_state', 'user_id', '=', 'id')
            ->where('arrived', '=', 1);

        if (!is_null($working)) {
            // @codeCoverageIgnoreStart
            $query
                ->leftJoin('UserWorkLog', 'UserWorkLog.user_id', '=', 'users.id')
                ->leftJoin('ShiftEntry', 'ShiftEntry.UID', '=', 'users.id')
                ->groupBy('users.id');

            $query->where(function ($query) use ($working) {
                /** @var QueryBuilder $query */
                if ($working) {
                    $query
                        ->whereNotNull('ShiftEntry.SID')
                        ->orWhereNotNull('UserWorkLog.work_hours');

                    return;
                }
                $query
                    ->whereNull('ShiftEntry.SID')
                    ->whereNull('UserWorkLog.work_hours');
            });
            // @codeCoverageIgnoreEnd
        }

        return $query
            ->count();
    }

    /**
     * The number of not arrived users
     *
     * @return int
     */
    public function newUsers(): int
    {
        return $this
            ->getQuery('users')
            ->join('users_state', 'user_id', '=', 'id')
            ->where('arrived', '=', 0)
            ->count();
    }

    /**
     * The number of currently working users
     *
     * @param null $freeloaded
     * @return int
     * @codeCoverageIgnore
     */
    public function currentlyWorkingUsers($freeloaded = null): int
    {
        $query = $this
            ->getQuery('users')
            ->join('ShiftEntry', 'ShiftEntry.UID', '=', 'users.id')
            ->join('Shifts', 'Shifts.SID', '=', 'ShiftEntry.SID')
            ->where('Shifts.start', '<=', time())
            ->where('Shifts.end', '>', time());

        if (!is_null($freeloaded)) {
            $query->where('ShiftEntry.freeloaded', '=', $freeloaded);
        }

        return $query->count();
    }

    /**
     * The number of worked shifts
     *
     * @param bool|null $done
     * @param bool|null $freeloaded
     * @return int
     * @codeCoverageIgnore
     */
    public function workSeconds($done = null, $freeloaded = null): int
    {
        $query = $this
            ->getQuery('ShiftEntry')
            ->join('Shifts', 'Shifts.SID', '=', 'ShiftEntry.SID');

        if (!is_null($freeloaded)) {
            $query->where('freeloaded', '=', $freeloaded);
        }

        if (!is_null($done)) {
            $query->where('end', ($done == true ? '<' : '>='), time());
        }

        return $query->sum($this->raw('end - start'));
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQuery(string $table): QueryBuilder
    {
        return $this->db
            ->getConnection()
            ->table($table);
    }

    /**
     * @param mixed $value
     * @return QueryExpression
     * @codeCoverageIgnore
     */
    protected function raw($value)
    {
        return $this->db->getConnection()->raw($value);
    }
}
