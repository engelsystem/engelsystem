<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Metrics;

use Carbon\Carbon;
use Engelsystem\Database\Database;
use Engelsystem\Models\EventConfig;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression as QueryExpression;
use Illuminate\Support\Collection;

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
     * @param bool|null $working
     * @return int
     */
    public function arrivedUsers(bool $working = null): int
    {
        $query = State::whereArrived(true);

        if (!is_null($working)) {
            // @codeCoverageIgnoreStart
            $query
                ->leftJoin('UserWorkLog', 'UserWorkLog.user_id', '=', 'users_state.user_id')
                ->leftJoin('ShiftEntry', 'ShiftEntry.UID', '=', 'users_state.user_id')
                ->distinct();

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

        return $query->count('users_state.user_id');
    }

    /**
     * The number of not arrived users
     *
     * @return int
     */
    public function newUsers(): int
    {
        return State::whereArrived(false)->count();
    }

    /**
     * @return int
     */
    public function forceActiveUsers(): int
    {
        return State::whereForceActive(true)->count();
    }

    /**
     * The number of currently working users
     *
     * @param bool|null $freeloaded
     * @return int
     * @codeCoverageIgnore
     */
    public function currentlyWorkingUsers(bool $freeloaded = null): int
    {
        $query = User::query()
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
        return (int)State::query()->sum('got_voucher');
    }

    /**
     * @return int
     */
    public function tshirts(): int
    {
        return State::whereGotShirt(true)->count();
    }

    /**
     * @return Collection
     */
    public function tshirtSizes(): Collection
    {
        return PersonalData::query()
            ->select(['shirt_size', $this->raw('COUNT(shirt_size) AS count')])
            ->whereNotNull('shirt_size')
            ->groupBy(['shirt_size'])
            ->get();
    }

    /**
     * @return Collection
     */
    public function languages(): Collection
    {
        return Settings::query()
            ->select(['language', $this->raw('COUNT(language) AS count')])
            ->groupBy(['language'])
            ->get();
    }

    /**
     * @return Collection
     */
    public function themes(): Collection
    {
        return Settings::query()
            ->select(['theme', $this->raw('COUNT(theme) AS count')])
            ->groupBy(['theme'])
            ->get();
    }

    /**
     * @param string $vehicle
     * @return int
     * @codeCoverageIgnore
     */
    public function licenses(string $vehicle = null): int
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
    public function workSeconds(bool $done = null, bool $freeloaded = null): int
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

        return (int)$query->sum($this->raw('end - start'));
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function worklogSeconds(): int
    {
        return (int)$this
            ->getQuery('UserWorkLog')
            ->sum($this->raw('work_hours * 60*60'));
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function shifts(): int
    {
        return $this
            ->getQuery('Shifts')
            ->count();
    }

    /**
     * @param bool|null $meeting
     * @return int
     */
    public function announcements(bool $meeting = null): int
    {
        $query = is_null($meeting) ? News::query() : News::whereIsMeeting($meeting);

        return $query->count();
    }

    /**
     * @param bool|null $answered
     * @return int
     */
    public function questions(bool $answered = null): int
    {
        $query = Question::query();
        if (!is_null($answered)) {
            if ($answered) {
                $query->whereNotNull('answerer_id');
            } else {
                $query->whereNull('answerer_id');
            }
        }

        return $query->count();
    }

    /**
     * @return int
     */
    public function messages(): int
    {
        return Message::query()->count();
    }

    /**
     * @return int
     */
    public function sessions(): int
    {
        return $this
            ->getQuery('sessions')
            ->count();
    }

    /**
     * @return float
     */
    public function databaseRead(): float
    {
        $start = microtime(true);

        (new EventConfig())->findOrNew('last_metrics');

        return microtime(true) - $start;
    }

    /**
     * @return float
     */
    public function databaseWrite(): float
    {
        $config = (new EventConfig())->findOrNew('last_metrics');
        $config
            ->setAttribute('name', 'last_metrics')
            ->setAttribute('value', new Carbon());

        $start = microtime(true);

        $config->save();

        return microtime(true) - $start;
    }

    /**
     * @param string|null $level
     * @return int
     */
    public function logEntries(string $level = null): int
    {
        $query = is_null($level) ? LogEntry::query() : LogEntry::whereLevel($level);

        return $query->count();
    }

    /**
     * @return int
     */
    public function passwordResets(): int
    {
        return PasswordReset::query()->count();
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
    protected function raw($value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
