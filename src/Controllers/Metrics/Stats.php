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
        /** @var QueryBuilder $query */
        $query = $this
            ->getQuery('users')
            ->join('users_state', 'user_id', '=', 'id')
            ->where('arrived', '=', 1);

        if (!is_null($working)) {
            // @codeCoverageIgnoreStart
            $query
                ->leftJoin('UserWorkLog', 'UserWorkLog.user_id', '=', 'users.id')
                ->leftJoin('ShiftEntry', 'ShiftEntry.UID', '=', 'users.id')
                ->distinct();

            $query->where(function ($query) use ($working) {
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
            ->count('users.id');
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
     * @return int
     */
    public function vouchers(): int
    {
        return $this
            ->getQuery('users_state')
            ->sum('got_voucher');
    }

    /**
     * @return int
     */
    public function tshirts(): int
    {
        return $this
            ->getQuery('users_state')
            ->where('got_shirt', '=', true)
            ->count();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function tshirtSizes()
    {
        return $this
            ->getQuery('users_personal_data')
            ->select(['shirt_size', $this->raw('COUNT(shirt_size) AS count')])
            ->whereNotNull('shirt_size')
            ->groupBy('shirt_size')
            ->get();
    }

    /**
     * @param string $vehicle
     * @return int
     * @codeCoverageIgnore
     */
    public function licenses($vehicle = null)
    {
        $mapping = [
            'forklift' => 'has_license_forklift',
            'car'      => 'has_license_car',
            '3.5t'     => 'has_license_3_5t_transporter',
            '7.5t'     => 'has_license_7_5t_truck',
            '12.5t'    => 'has_license_12_5t_truck',
        ];

        $query = $this
            ->getQuery('UserDriverLicenses');

        if (!is_null($vehicle)) {
            $query->where($mapping[$vehicle], '=', true);
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
     * @return int
     * @codeCoverageIgnore
     */
    public function worklogSeconds()
    {
        return round($this
            ->getQuery('UserWorkLog')
            ->sum($this->raw('work_hours * 60*60')));
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function shifts()
    {
        return $this
            ->getQuery('Shifts')
            ->count();
    }

    /**
     * @param bool $meeting
     * @return int
     * @codeCoverageIgnore
     */
    public function announcements($meeting = null)
    {
        $query = $this
            ->getQuery('News');

        if (!is_null($meeting)) {
            $query->where('Treffen', '=', $meeting);
        }

        return $query->count();
    }

    /**
     * @param bool $answered
     * @return int
     * @codeCoverageIgnore
     */
    public function questions($answered = null)
    {
        $query = $this
            ->getQuery('Questions');

        if (!is_null($answered)) {
            if ($answered) {
                $query->whereNotNull('AID');
            } else {
                $query->whereNull('AID');
            }
        }

        return $query->count();
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function messages()
    {
        return $this
            ->getQuery('Messages')
            ->count();
    }

    /**
     * @return int
     */
    public function sessions()
    {
        return $this
            ->getQuery('sessions')
            ->count();
    }

    /**
     * @param string $level
     * @return int
     */
    public function logEntries($level = null)
    {
        $query = $this
            ->getQuery('log_entries');

        if (!is_null($level)) {
            $query->where('level', '=', $level);
        }

        return $query->count();
    }

    /**
     * @return int
     */
    public function passwordResets()
    {
        return $this
            ->getQuery('password_resets')
            ->count();
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
     */
    protected function raw($value)
    {
        return $this->db->getConnection()->raw($value);
    }
}
