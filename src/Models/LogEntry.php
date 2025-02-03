<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $level
 * @property string      $message
 * @property Carbon|null $created_at
 *
 * @property-read User|null $user
 *
 * @method static QueryBuilder|LogEntry[] whereId($value)
 * @method static QueryBuilder|LogEntry[] whereLevel($value)
 * @method static QueryBuilder|LogEntry[] whereMessage($value)
 * @method static QueryBuilder|LogEntry[] whereCreatedAt($value)
 */
class LogEntry extends BaseModel
{
    use UsesUserModel;
    use HasFactory;

    /** @var bool enable timestamps for created_at */
    public $timestamps = true; // phpcs:ignore

    /** @var null Disable updated_at */
    public const UPDATED_AT = null;

    /** @var array Default attributes */
    protected $attributes = [ // phpcs:ignore
        'user_id' => null,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ // phpcs:ignore
        'level',
        'message',
        'user_id',
    ];

    /**
     * @return Builder[]|Collection|SupportCollection|LogEntry[]
     */
    public static function filter(
        ?string $keyword = null,
        ?int $userId = null,
        ?string $level = null
    ): array | Collection | SupportCollection {
        $query = self::with(['user', 'user.personalData', 'user.state'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10000);

        if (!empty($userId)) {
            $query->where(function (Builder $query) use ($userId): void {
                $user = User::findOrFail($userId);
                $query->where('user_id', $userId)
                    ->orWhere('message', 'like', '%' . $user->name . ' (' . $userId . ')%');
            });
        }

        if (!empty($level)) {
            $query->where('level', '=', $level);
        }

        if (!empty($keyword)) {
            $query->where('message', 'LIKE', '%' . $keyword . '%');
        }

        return $query->get();
    }
}
