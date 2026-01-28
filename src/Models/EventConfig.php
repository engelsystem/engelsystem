<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Helpers\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string $name
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static QueryBuilder|EventConfig[] whereName($value)
 * @method static QueryBuilder|EventConfig[] whereValue($value)
 * @method static QueryBuilder|EventConfig[] whereCreatedAt($value)
 * @method static QueryBuilder|EventConfig[] whereUpdatedAt($value)
 */
class EventConfig extends BaseModel
{
    /** @var string The primary key for the model */
    protected $primaryKey = 'name'; // phpcs:ignore

    /** @var bool Indicates if the IDs are auto-incrementing */
    public $incrementing = false; // phpcs:ignore

    /** @var string Required because it is not event_configs */
    protected $table = 'event_config'; // phpcs:ignore

    /** @var array Values that are mass assignable */
    protected $fillable = ['name', 'value']; // phpcs:ignore

    /** @var bool It could be interesting to know when a value changed the last time */
    public $timestamps = true; // phpcs:ignore

    public function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
