<?php

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string $name
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static QueryBuilder|EventConfig[] whereName($value)
 * @method static QueryBuilder|EventConfig[] whereValue($value)
 * @method static QueryBuilder|EventConfig[] whereCreatedAt($value)
 * @method static QueryBuilder|EventConfig[] whereUpdatedAt($value)
 */
class EventConfig extends BaseModel
{
    /** @var string The primary key for the model */
    protected $primaryKey = 'name';

    /** @var bool Indicates if the IDs are auto-incrementing */
    public $incrementing = false;

    /** @var string Required because it is not event_configs */
    protected $table = 'event_config';

    /** @var array Values that are mass assignable */
    protected $fillable = ['name', 'value'];

    /** @var array The configuration values that should be cast to native types */
    protected $valueCasts = [
        'buildup_start' => 'datetime_human',
        'event_start'   => 'datetime_human',
        'event_end'     => 'datetime_human',
        'teardown_end'  => 'datetime_human',
        'last_metrics'  => 'datetime',
    ];

    /** @var bool It could be interesting to know when a value changed the last time */
    public $timestamps = true;

    /**
     * Value accessor
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        $value = $value ? $this->fromJson($value) : null;

        /** @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute */
        if (!empty($value)) {
            switch ($this->getValueCast($this->name)) {
                case 'datetime_human':
                    return Carbon::make($value);
                case 'datetime':
                    return Carbon::createFromFormat(Carbon::ISO8601, $value);
            }
        }

        return $value;
    }

    /**
     * Value mutator
     *
     * @param mixed $value
     * @return static
     */
    public function setValueAttribute($value)
    {
        if (!empty($value)) {
            switch ($this->getValueCast($this->name)) {
                case 'datetime_human':
                    /** @var Carbon $value */
                    $value = $value->toDateTimeString('minute');
                    break;
                case 'datetime':
                    /** @var Carbon $value */
                    $value = $value->toIso8601String();
                    break;
            }
        }

        $value = $this->castAttributeAsJson('value', $value);
        $this->attributes['value'] = $value;

        return $this;
    }

    /**
     * Check if the value has to be casted
     *
     * @param string $value
     * @return string|null
     */
    protected function getValueCast($value)
    {
        return isset($this->valueCasts[$value]) ? $this->valueCasts[$value] : null;
    }
}
