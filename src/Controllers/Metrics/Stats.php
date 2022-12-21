<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Metrics;

use Carbon\Carbon;
use Engelsystem\Database\Database;
use Engelsystem\Models\EventConfig;
use Engelsystem\Models\Faq;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Question;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression as QueryExpression;
use Illuminate\Support\Collection;

class Stats
{
    public function __construct(protected Database $db)
    {
    }

    /**
     * The number of not arrived users
     *
     * @param bool|null $working
     */
    public function arrivedUsers(bool $working = null): int
    {
        $query = State::whereArrived(true);

        if (!is_null($working)) {
            // @codeCoverageIgnoreStart
            $query
                ->leftJoin('worklogs', 'worklogs.user_id', '=', 'users_state.user_id')
                ->leftJoin('ShiftEntry', 'ShiftEntry.UID', '=', 'users_state.user_id')
                ->distinct();

            $query->where(function ($query) use ($working): void {
                /** @var QueryBuilder $query */
                if ($working) {
                    $query
                        ->whereNotNull('ShiftEntry.SID')
                        ->orWhereNotNull('worklogs.hours');

                    return;
                }

                $query
                    ->whereNull('ShiftEntry.SID')
                    ->whereNull('worklogs.hours');
            });
            // @codeCoverageIgnoreEnd
        }

        return $query->count('users_state.user_id');
    }

    /**
     * The number of not arrived users
     */
    public function newUsers(): int
    {
        return State::whereArrived(false)->count();
    }

    public function forceActiveUsers(): int
    {
        return State::whereForceActive(true)->count();
    }

    public function usersPronouns(): int
    {
        return PersonalData::where('pronoun', '!=', '')->count();
    }

    public function email(string $type): int
    {
        return match ($type) {
            'system' => Settings::whereEmailShiftinfo(true)->count(),
            'humans' => Settings::whereEmailHuman(true)->count(),
            'goody'  => Settings::whereEmailGoody(true)->count(),
            'news'   => Settings::whereEmailNews(true)->count(),
            default  => 0,
        };
    }

    /**
     * The number of currently working users
     *
     * @param bool|null $freeloaded
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

    protected function vouchersQuery(): Builder
    {
        return State::query();
    }

    public function vouchers(): int
    {
        return (int)$this->vouchersQuery()->sum('got_voucher');
    }

    public function vouchersBuckets(array $buckets): array
    {
        $return = [];
        foreach ($buckets as $bucket) {
            $query = $this->vouchersQuery();

            if ($bucket !== '+Inf') {
                $query->where('got_voucher', '<=', $bucket);
            }

            $return[$bucket] = $query->count('got_voucher');
        }

        return $return;
    }

    public function tshirts(): int
    {
        return State::whereGotShirt(true)->count();
    }

    public function tshirtSizes(): Collection
    {
        return PersonalData::query()
            ->select(['shirt_size', $this->raw('COUNT(shirt_size) AS count')])
            ->whereNotNull('shirt_size')
            ->groupBy(['shirt_size'])
            ->get();
    }

    public function languages(): Collection
    {
        return Settings::query()
            ->select(['language', $this->raw('COUNT(language) AS count')])
            ->groupBy(['language'])
            ->get();
    }

    public function themes(): Collection
    {
        return Settings::query()
            ->select(['theme', $this->raw('COUNT(theme) AS count')])
            ->groupBy(['theme'])
            ->get();
    }

    /**
     * @param string|null $vehicle
     */
    public function licenses(string $vehicle): int
    {
        $mapping = [
            'has_car'  => 'has_car',
            'forklift' => 'drive_forklift',
            'car'      => 'drive_car',
            '3.5t'     => 'drive_3_5t',
            '7.5t'     => 'drive_7_5t',
            '12t'      => 'drive_12t',
        ];

        $query = (new License())
            ->getQuery()
            ->where($mapping[$vehicle], true);

        return $query->count();
    }

    /**
     * @param bool|null $done
     * @param bool|null $freeloaded
     *
     * @codeCoverageIgnore
     */
    protected function workSecondsQuery(bool $done = null, bool $freeloaded = null): QueryBuilder
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

        return $query;
    }

    /**
     * The amount of worked seconds
     *
     * @param bool|null $done
     * @param bool|null $freeloaded
     *
     * @codeCoverageIgnore
     */
    public function workSeconds(bool $done = null, bool $freeloaded = null): int
    {
        $query = $this->workSecondsQuery($done, $freeloaded);

        return (int)$query->sum($this->raw('end - start'));
    }

    /**
     * The number of worked shifts
     *
     * @param bool|null $done
     * @param bool|null $freeloaded
     *
     * @codeCoverageIgnore
     */
    public function workBuckets(array $buckets, bool $done = null, bool $freeloaded = null): array
    {
        return $this->getBuckets(
            $buckets,
            $this->workSecondsQuery($done, $freeloaded),
            'UID',
            'SUM(end - start)',
            'SUM(end - start)'
        );
    }

    /**
     * @codeCoverageIgnore As long as its only used for old tables
     */
    protected function getBuckets(
        array $buckets,
        BuilderContract $basicQuery,
        string $groupBy,
        string $having,
        string $count
    ): array {
        $return = [];

        foreach ($buckets as $bucket) {
            $query = clone $basicQuery;
            $query->groupBy($groupBy);

            if ($bucket !== '+Inf') {
                $query->having($this->raw($having), '<=', $bucket);
            }

            $return[$bucket] = count($query->get($this->raw($count)));
        }

        return $return;
    }

    /**
     * @codeCoverageIgnore
     */
    public function worklogSeconds(): int
    {
        return (int)Worklog::query()
            ->sum($this->raw('hours * 60 * 60'));
    }

    /**
     * @codeCoverageIgnore
     */
    public function worklogBuckets(array $buckets): array
    {
        return $this->getBuckets(
            $buckets,
            Worklog::query(),
            'user_id',
            'SUM(hours * 60 * 60)',
            'SUM(hours * 60 * 60)'
        );
    }

    public function rooms(): int
    {
        return Room::query()
            ->count();
    }

    /**
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
     */
    public function announcements(bool $meeting = null): int
    {
        $query = is_null($meeting) ? News::query() : News::whereIsMeeting($meeting);

        return $query->count();
    }

    public function comments(): int
    {
        return NewsComment::query()
            ->count();
    }

    /**
     * @param bool|null $answered
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

    public function faq(): int
    {
        return Faq::query()->count();
    }

    public function messages(): int
    {
        return Message::query()->count();
    }

    public function sessions(): int
    {
        return $this
            ->getQuery('sessions')
            ->count();
    }

    public function oauth(): Collection
    {
        return OAuth::query()
            ->select(['provider', $this->raw('COUNT(provider) AS count')])
            ->groupBy(['provider'])
            ->get();
    }

    public function databaseRead(): float
    {
        $start = microtime(true);

        (new EventConfig())->findOrNew('last_metrics');

        return microtime(true) - $start;
    }

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
     */
    public function logEntries(string $level = null): int
    {
        $query = is_null($level) ? LogEntry::query() : LogEntry::whereLevel($level);

        return $query->count();
    }

    public function passwordResets(): int
    {
        return PasswordReset::query()->count();
    }

    protected function getQuery(string $table): QueryBuilder
    {
        return $this->db
            ->getConnection()
            ->table($table);
    }

    protected function raw(mixed $value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
