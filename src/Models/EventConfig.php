<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use DateTimeInterface;
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

    /** @var array<string, string> The configuration values that should be cast to native types */
    protected array $valueCasts = [
        'buildup_start' => 'datetime_human',
        'event_start'   => 'datetime_human',
        'event_end'     => 'datetime_human',
        'teardown_end'  => 'datetime_human',
        'last_metrics'  => 'datetime',
    ];

    /** @var bool It could be interesting to know when a value changed the last time */
    public $timestamps = true; // phpcs:ignore

    /**
     * Value accessor
     */
    public function getValueAttribute(mixed $value): mixed
    {
        $value = $value ? $this->fromJson($value) : null;

        /** @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute */
        if (!empty($value)) {
            return match ($this->getValueCast($this->name)) {
                'datetime_human' => Carbon::make($value),
                'datetime'       => Carbon::createFromFormat(DateTimeInterface::ATOM, $value),
                default          => $value,
            };
        }

        return $value;
    }

    /**
     * Value mutator
     *
     * @return static
     */
    public function setValueAttribute(mixed $value): static
    {
        if (!empty($value)) {
            /** @var Carbon $value */
            $value = match ($this->getValueCast($this->name)) {
                'datetime_human' => $value->toDateTimeString('minute'),
                'datetime'       => $value->format(DateTimeInterface::ATOM),
                default          => $value,
            };
        }

        $value = $this->castAttributeAsJson('value', $value);
        $this->attributes['value'] = $value;

        return $this;
    }

    /**
     * Check if the value has to be casted
     */
    protected function getValueCast(string $value): ?string
    {
        return $this->valueCasts[$value] ?? null;
    }
}
